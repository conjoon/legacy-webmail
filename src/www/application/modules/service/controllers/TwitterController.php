<?php
/**
 * conjoon
 * (c) 2002-2012 siteartwork.de/conjoon.org
 * licensing@conjoon.org
 *
 * $Author$
 * $Id$
 * $Date$
 * $Revision$
 * $LastChangedDate$
 * $LastChangedBy$
 * $URL$
 */

/**
 * Zend_Controller_Action
 */
require_once 'Zend/Controller/Action.php';

/**
 *
 * @author Thorsten Suckow-Homberg <ts@siteartwork.de>
 */
class Service_TwitterController extends Zend_Controller_Action {

    const CONTEXT_JSON = 'json';

    /**
     * Inits this controller and sets the context-switch-directives
     * on the various actions.
     *
     */
    public function init()
    {
        $conjoonContext = $this->_helper->conjoonContext();

        $conjoonContext->addActionContext('get.recent.tweets',       self::CONTEXT_JSON)
                       ->addActionContext('get.friends',             self::CONTEXT_JSON)
                       ->addActionContext('send.update',             self::CONTEXT_JSON)
                       ->addActionContext('delete.tweet',            self::CONTEXT_JSON)
                       ->addActionContext('favorite.tweet',          self::CONTEXT_JSON)
                       ->addActionContext('switch.friendship',       self::CONTEXT_JSON)
                       ->addActionContext('get.users.recent.tweets', self::CONTEXT_JSON)
                       ->initContext();
    }

    /**
     * Sends a list of recent tweets for the specified account to the client.
     * Expects the parameter "id" which holds the id of the configured account
     * stored in the database.
     *
     */
    public function getRecentTweetsAction()
    {
        /*@REMOVE@*/
        if (!$this->_helper->connectionCheck()) {
            $this->view->success = true;
            $this->view->tweets  = array();
            $this->view->error   = null;

            return;
        }
        /*@REMOVE@*/

        /**
         * @see Conjoon_Error_Factory
         */
        require_once 'Conjoon/Error/Factory.php';

        $accountId = (int)$this->_request->getParam('id');

        if ($accountId <= 0) {
            $errorDto = Conjoon_Error_Factory::createError(
                "Could not send the status update: No account-id provided.", Conjoon_Error::LEVEL_ERROR
            )->getDto();
            $this->view->success = false;
            $this->view->tweets  = array();
            $this->view->error   = $errorDto;
            return;
        }

        require_once 'Conjoon/BeanContext/Decorator.php';
        $decoratedModel = new Conjoon_BeanContext_Decorator(
            'Conjoon_Modules_Service_Twitter_Account_Model_Account'
        );

        $accountDto = $decoratedModel->getAccountAsDto($accountId);

        if (!$accountDto) {
            $errorDto = Conjoon_Error_Factory::createError(
                "Could not retrieve tweets: No account matches the id \"".$accountId."\".", Conjoon_Error::LEVEL_CRITICAL
            )->getDto();
            $this->view->success = false;
            $this->view->tweets  = array();
            $this->view->error   = $errorDto;
            return;
        }

        /**
         * @see Conjoon_Service_Twitter_Proxy
         */
        require_once 'Conjoon/Service/Twitter/Proxy.php';

        $twitter = new Conjoon_Service_Twitter_Proxy(array(
             'oauth_token'        => $accountDto->oauthToken,
             'oauth_token_secret' => $accountDto->oauthTokenSecret,
             'user_id'            => $accountDto->twitterId,
             'screen_name'        => $accountDto->name
        ));

        $tweets = $twitter->statusFriendsTimeline();
        $twitter->accountEndSession();

        if ($tweets instanceof Conjoon_Error) {
            $this->view->success = false;
            $this->view->tweets  = array();
            $this->view->error   = $tweets->getDto();
            return;
        }

        $dtoTweets = array();

        for ($i = 0, $len = count($tweets); $i < $len; $i++) {
            $dtoTweets[] = $tweets[$i]->getDto();
        }

        $this->view->success = true;
        $this->view->tweets  = $dtoTweets;
        $this->view->error   = null;

    }

    /**
     * Sends a list of friends for the specified account to the client.
     * Expects the parameter "id" which holds the id of the configured account
     * stored in the database.
     *
     */
    public function getFriendsAction()
    {
        $accountId = (int)$this->_request->getParam('id');

        if ($accountId <= 0) {
            $this->view->success = false;
            $this->view->tweets  = array();
            $this->view->error   = null;
            return;
        }

        require_once 'Conjoon/BeanContext/Decorator.php';
        $decoratedModel = new Conjoon_BeanContext_Decorator(
            'Conjoon_Modules_Service_Twitter_Account_Model_Account'
        );

        $accountDto = $decoratedModel->getAccountAsDto($accountId);

        if (!$accountDto) {
            $this->view->success = false;
            $this->view->tweets  = array();
            $this->view->error   = null;
            return;
        }

        require_once 'Conjoon/Service/Twitter.php';

        /**
         * @see Zend_Oauth_Token_Access
         */
        require_once 'Zend/Oauth/Token/Access.php';

        $accessToken = new Zend_Oauth_Token_Access();
        $accessToken->setParams(array(
            'oauth_token'        => $accountDto->oauthToken,
            'oauth_token_secret' => $accountDto->oauthTokenSecret,
            'user_id'            => $accountDto->twitterId,
            'screen_name'        => $accountDto->name,
        ));

        $twitter = new Conjoon_Service_Twitter(array(
            'username'    => $accountDto->name,
            'accessToken' => $accessToken
        ));

        $cursor = -1;

        $users = array();

        while ($cursor != 0) {

            $friends = $twitter->userFriends(array(
                'id'   => $accountDto->twitterId,
                'cursor' => $cursor
            ));

            if ($friends->user) {

                // looks like we won't get an array if the twitter user
                // has only one friend. instead we'll get directly a
                // SimpleXMLElement
                if (!is_array($friends->user)) {
                    $friends->user = array($friends->user);
                }

                foreach ($friends->user as $friend) {

                    $users[] = array(
                        'id'              => (string)$friend->id,
                        'name'            => (string)$friend->name,
                        'screenName'      => (string)$friend->screen_name,
                        'location'        => (string)$friend->location,
                        'profileImageUrl' => (string)$friend->profile_image_url,
                        'url'             => (string)$friend->url,
                        'description'     => (string)$friend->description,
                        'protected'       => (string)$friend->protected,
                        'followersCount'  => (int)(string)$friend->followers_count
                    );
                }
            } else {
                break;
            }

            $cursor = (string)$friends->next_cursor;
        }

        $twitter->account->endSession();

        $this->view->success = true;
        $this->view->users   = $users;
        $this->view->error   = null;
    }

    /**
     * Sends a list of recent tweets for a Twitter user which id is specified
     * in the request parameter "userId". the account which triggered this request
     * is specified in the request parameter "id".
     * If the parameter statusId is supplied, only this single entry will be returned.
     *
     */
    public function getUsersRecentTweetsAction()
    {
        /**
         * @see Conjoon_Error_Factory
         */
        require_once 'Conjoon/Error/Factory.php';

        $userId    = (string)$this->_request->getParam('userId');
        $accountId = (int)$this->_request->getParam('id');
        $userName  = (string)$this->_request->getParam('userName');
        $statusId  = (string)$this->_request->getParam('statusId');

        if ($userName != "" && $userId <= 0) {
            $userId = $userName;
        }

        if ($accountId <= 0 || (!is_string($userId) && $userId <= 0)) {
            $errorTxt = $accountId <= 0
                        ? "Could not receive tweets: No account-id provided."
                        : "Could not receive tweets: No user-id or screen-name provided.";

            $errorDto = Conjoon_Error_Factory::createError(
                $errorTxt, Conjoon_Error::LEVEL_ERROR
            )->getDto();
            $this->view->success = false;
            $this->view->tweets  = array();
            $this->view->error   = $errorDto;
            return;
        }

        require_once 'Conjoon/BeanContext/Decorator.php';
        $decoratedModel = new Conjoon_BeanContext_Decorator(
            'Conjoon_Modules_Service_Twitter_Account_Model_Account'
        );

        $accountDto = $decoratedModel->getAccountAsDto($accountId);

        if (!$accountDto) {
            $errorDto = Conjoon_Error_Factory::createError(
                "Could not retrieve tweets: No account matches the id \"".$accountId."\".", Conjoon_Error::LEVEL_CRITICAL
            )->getDto();
            $this->view->success = false;
            $this->view->tweets  = array();
            $this->view->error   = $errorDto;
            return;
        }

        /**
         * @see Conjoon_Service_Twitter_Proxy
         */
        require_once 'Conjoon/Service/Twitter/Proxy.php';


        $twitter = new Conjoon_Service_Twitter_Proxy(array(
            'oauth_token'        => $accountDto->oauthToken,
            'oauth_token_secret' => $accountDto->oauthTokenSecret,
            'user_id'            => $accountDto->twitterId,
            'screen_name'        => $accountDto->name
        ));

        if ($statusId > 0) {
            $tweets = $twitter->statusShow($statusId);
        } else {

            $ps = is_numeric($userId)
                  ? array('id'          => $userId)
                  : array('screen_name' => $userId);

            $tweets = $twitter->statusUserTimeline($ps);
        }

        $twitter->accountEndSession();

        if ($tweets instanceof Conjoon_Error) {
            $this->view->success = false;
            $this->view->tweets  = array();
            $this->view->error   = $tweets->getDto();
            return;
        }

        $dtoTweets = array();

        if ($statusId > 0) {
            $dtoTweets[] = $tweets->getDto();
        } else {
            for ($i = 0, $len = count($tweets); $i < $len; $i++) {
                $dtoTweets[] = $tweets[$i]->getDto();
            }
        }

        $this->view->success = true;
        $this->view->tweets  = $dtoTweets;
        $this->view->error   = null;
    }


    /**
     * Sends a message to Twitter for the configured account. The account
     * used to send the message is passed as an id in accountId
     *
     */
    public function sendUpdateAction()
    {
        /**
         * @see Conjoon_Error_Factory
         */
        require_once 'Conjoon/Error/Factory.php';

        $accountId         = (int)$this->_request->getParam('accountId');
        $inReplyToStatusId = (string)$this->_request->getParam('inReplyToStatusId');
        $message           = (string)$this->_request->getParam('message');

        if ($accountId <= 0) {
            $errorDto = Conjoon_Error_Factory::createError(
                "Could not send the status update: No account-id provided.", Conjoon_Error::LEVEL_ERROR
            )->getDto();

            $this->view->success = false;
            $this->view->tweets  = array();
            $this->view->error   = $errorDto;
            return;
        }

        require_once 'Conjoon/BeanContext/Decorator.php';
        $decoratedModel = new Conjoon_BeanContext_Decorator(
            'Conjoon_Modules_Service_Twitter_Account_Model_Account'
        );

        $accountDto = $decoratedModel->getAccountAsDto($accountId);

        if (!$accountDto) {
            $errorDto = Conjoon_Error_Factory::createError(
                "Could not send the status update: No account matches the id \"".$accountId."\".", Conjoon_Error::LEVEL_CRITICAL
            )->getDto();

            $this->view->success = false;
            $this->view->tweets  = array();
            $this->view->error   = $errorDto;
            return;
        }

        require_once 'Conjoon/Service/Twitter/Proxy.php';

        $twitter = new Conjoon_Service_Twitter_Proxy(array(
            'oauth_token'        => $accountDto->oauthToken,
            'oauth_token_secret' => $accountDto->oauthTokenSecret,
            'user_id'            => $accountDto->twitterId,
            'screen_name'        => $accountDto->name
        ));

        // check inReplyToStatusId and set to null if necessary
        $inReplyToStatusId = $inReplyToStatusId > 0 ? $inReplyToStatusId : null;

        $result  = $twitter->statusUpdate($message, $inReplyToStatusId);
        $twitter->accountEndSession();

        if ($result instanceof Conjoon_Error) {
            $this->view->success = false;
            $this->view->tweets  = array();
            $this->view->error   = $result->getDto();
            return;
        }

        $this->view->success = true;
        $this->view->tweet   = $result->getDto();
        $this->view->error   = null;
    }

    /**
     * requests to delete a tweet with a specific id.
     * The acount used to request the delete is passed as an id in accountId,
     * the id of the tweet to delete is passed in the parameter tweetId.
     *
     */
    public function deleteTweetAction()
    {
        /**
         * @see Conjoon_Error_Factory
         */
        require_once 'Conjoon/Error/Factory.php';

        $accountId  = (int)$this->_request->getParam('accountId');
        $tweetId    = (string)$this->_request->getParam('tweetId');

        if ($accountId <= 0 || $tweetId <= 0) {
            $errorDto = Conjoon_Error_Factory::createError(
                (($accountId <= 0)
                ? "Could not delete the tweet: No account-id provided."
                : "Could not delete the tweet: No tweet specified."),
                Conjoon_Error::LEVEL_ERROR
            )->getDto();

            $this->view->success      = false;
            $this->view->deletedTweet = null;
            $this->view->error        = $errorDto;
            return;
        }

        require_once 'Conjoon/BeanContext/Decorator.php';
        $decoratedModel = new Conjoon_BeanContext_Decorator(
            'Conjoon_Modules_Service_Twitter_Account_Model_Account'
        );

        $accountDto = $decoratedModel->getAccountAsDto($accountId);

        if (!$accountDto) {
            $errorDto = Conjoon_Error_Factory::createError(
                "Could not delete the tweet: No account matches the id \"".$accountId."\".", Conjoon_Error::LEVEL_CRITICAL
            )->getDto();

            $this->view->success      = false;
            $this->view->deletedTweet = null;
            $this->view->error        = $errorDto;
            return;
        }

        require_once 'Conjoon/Service/Twitter/Proxy.php';

        $twitter = new Conjoon_Service_Twitter_Proxy(array(
            'oauth_token'        => $accountDto->oauthToken,
            'oauth_token_secret' => $accountDto->oauthTokenSecret,
            'user_id'            => $accountDto->twitterId,
            'screen_name'        => $accountDto->name
        ));

        $result  = $twitter->deleteTweet($tweetId);
        $twitter->accountEndSession();

        if ($result instanceof Conjoon_Error) {
            $this->view->success      = false;
            $this->view->deletedTweet = null;
            $this->view->error        = $result->getDto();
            return;
        }

        $this->view->success      = true;
        $this->view->deletedTweet = $result->getDto();
        $this->view->error        = null;
    }

    /**
     * requests to favorite a tweet with a specific id.
     * The acount used to request the "favorite" is passed as an id in accountId,
     * the id of the tweet to favorite is passed in the parameter tweetId.
     * The boolean parameter "favorite" tells whether to "favorite" or
     * "un-favorite" the specified tweet.
     *
     */
    public function favoriteTweetAction()
    {
        /**
         * @see Conjoon_Error_Factory
         */
        require_once 'Conjoon/Error/Factory.php';

        $accountId  = (int)$this->_request->getParam('accountId');
        $tweetId    = (string)$this->_request->getParam('tweetId');
        /**
         * @todo Filter!!!
         */
        $favorite = !$this->_request->getParam('favorite')
                    ? false
                    : true;

        if ($accountId <= 0 || $tweetId <= 0) {
            $errorDto = Conjoon_Error_Factory::createError(
                (($accountId <= 0)
                ? "Could not process the request: No account-id provided."
                : "Could not process the request: No tweet specified."),
                Conjoon_Error::LEVEL_ERROR
            )->getDto();

            $this->view->success        = false;
            $this->view->favoritedTweet = null;
            $this->view->error          = $errorDto;
            return;
        }

        require_once 'Conjoon/BeanContext/Decorator.php';
        $decoratedModel = new Conjoon_BeanContext_Decorator(
            'Conjoon_Modules_Service_Twitter_Account_Model_Account'
        );

        $accountDto = $decoratedModel->getAccountAsDto($accountId);

        if (!$accountDto) {
            $errorDto = Conjoon_Error_Factory::createError(
                "Could not favorite the tweet: No account matches the id \"".$accountId."\".", Conjoon_Error::LEVEL_CRITICAL
            )->getDto();

            $this->view->success        = false;
            $this->view->favoritedTweet = null;
            $this->view->error          = $errorDto;
            return;
        }

        require_once 'Conjoon/Service/Twitter/Proxy.php';

        $twitter = new Conjoon_Service_Twitter_Proxy(array(
            'oauth_token'        => $accountDto->oauthToken,
            'oauth_token_secret' => $accountDto->oauthTokenSecret,
            'user_id'            => $accountDto->twitterId,
            'screen_name'        => $accountDto->name
        ));

        $result  = $twitter->favoriteTweet($tweetId, $favorite);
        $twitter->accountEndSession();

        if ($result instanceof Conjoon_Error) {
            $this->view->success        = false;
            $this->view->favoritedTweet = null;
            $this->view->error          = $result->getDto();
            return;
        }

        $this->view->success        = true;
        $this->view->favoritedTweet = $result->getDto();
        $this->view->error          = null;
    }


    /**
     * Switches a friendship to a user based on the parameter createFriendship and
     * the screen name of the user.
     *
     */
    public function switchFriendshipAction()
    {
        /**
         * @see Conjoon_Error_Factory
         */
        require_once 'Conjoon/Error/Factory.php';

        $accountId  = (int)$this->_request->getParam('accountId');

        $createFriendship = $this->_request->getParam('createFriendship') == 'false'
                            ? false
                            : true;
        $screenName       = $this->_request->getParam('screenName');


        if ($accountId <= 0) {
            $errorDto = Conjoon_Error_Factory::createError(
                "Could not process the request: No account-id provided.",
                Conjoon_Error::LEVEL_ERROR
            )->getDto();

            $this->view->success     = false;
            $this->view->isFollowing = !$createFriendship;
            $this->view->error       = $errorDto;
            return;
        }

        require_once 'Conjoon/BeanContext/Decorator.php';
        $decoratedModel = new Conjoon_BeanContext_Decorator(
            'Conjoon_Modules_Service_Twitter_Account_Model_Account'
        );

        $accountDto = $decoratedModel->getAccountAsDto($accountId);

        if (!$accountDto) {
            $errorDto = Conjoon_Error_Factory::createError(
                "Could not switch friendship: No account matches the id \"".$accountId."\".", Conjoon_Error::LEVEL_CRITICAL
            )->getDto();

            $this->view->success     = false;
            $this->view->isFollowing = !$createFriendship;
            $this->view->error       = $errorDto;
            return;
        }

        require_once 'Conjoon/Service/Twitter/Proxy.php';

        $twitter = new Conjoon_Service_Twitter_Proxy(array(
            'oauth_token'        => $accountDto->oauthToken,
            'oauth_token_secret' => $accountDto->oauthTokenSecret,
            'user_id'            => $accountDto->twitterId,
            'screen_name'        => $accountDto->name
        ));

        if ($createFriendship) {
            $result = $twitter->friendshipCreate($screenName);
        } else {
            $result = $twitter->friendshipDestroy($screenName);
        }

        if ($result instanceof Conjoon_Error) {
            $this->view->success     = false;
            $this->view->isFollowing = !$createFriendship;
            $this->view->error       = $result->getDto();
            return;
        }

        $this->view->success     = true;
        $this->view->isFollowing = $createFriendship;
        $this->view->error       = null;
    }


}