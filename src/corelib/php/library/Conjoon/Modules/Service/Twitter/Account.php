<?php
/**
 * conjoon
 * (c) 2007-2015 conjoon.org
 * licensing@conjoon.org
 *
 * conjoon
 * Copyright (C) 2014 Thorsten Suckow-Homberg/conjoon.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
 * @see Conjoon_BeanContext
 */
require_once 'Conjoon/BeanContext.php';

/**
 * @see Zend_Oauth_Token_Access
 */
require_once 'Zend/Oauth/Token/Access.php';

/**
 * @see Conjoon_Modules_Service_Twitter_Account_Dto
 */
require_once 'Conjoon/Modules/Service/Twitter/Account/Dto.php';

/**
 * A class representing a Twitter user account.
 *
 * @uses       Conjoon_BeanContext
 * @category   Conjoon_Service
 * @package    Conjoon_Service
 * @subpackage Feeds
 *
 * @author Thorsten-Suckow-Homberg <tsuckow@conjoon.org>
 */

class Conjoon_Modules_Service_Twitter_Account implements Conjoon_BeanContext, Serializable {

    private $id;
    private $userId;
    private $name;
    private $oauthToken;
    private $oauthTokenSecret;
    private $updateInterval;
    private $twitterId;
    private $twitterName;
    private $twitterScreenName;
    private $twitterLocation;
    private $twitterProfileImageUrl;
    private $twitterUrl;
    private $twitterProtected;
    private $twitterDescription;
    private $twitterFollowersCount;

    /**
     * Constructor.
     *
     */
    public function __construct()
    {
    }

// -------- accessors

    public function setId($id){$this->id = $id;}
    public function setUserId($userId){$this->userId = $userId;}
    public function setName($name){$this->name = $name;}
    public function setOauthToken($token){$this->oauthToken = $token;}
    public function setOauthTokenSecret($token){$this->oauthTokenSecret = $token;}
    public function setUpdateInterval($updateInterval){$this->updateInterval = $updateInterval;}
    public function setTwitterId($twitterId){$this->twitterId = $twitterId;}
    public function setTwitterName($twitterName){$this->twitterName = $twitterName;}
    public function setTwitterScreenName($twitterScreenName){$this->twitterScreenName = $twitterScreenName;}
    public function setTwitterLocation($twitterLocation){$this->twitterLocation = $twitterLocation;}
    public function setTwitterProfileImageUrl($twitterProfileImageUrl){$this->twitterProfileImageUrl = $twitterProfileImageUrl;}
    public function setTwitterUrl($twitterUrl){$this->twitterUrl = $twitterUrl;}
    public function setTwitterProtected($twitterProtected){$this->twitterProtected = $twitterProtected;}
    public function setTwitterDescription($twitterProtected){$this->twitterProtected = $twitterProtected;}
    public function setTwitterFollowersCount($twitterFollowersCount){$this->twitterFollowersCount = $twitterFollowersCount;}

    public function getId(){return $this->id;}
    public function getUserId(){return $this->userId;}
    public function getName(){return $this->name;}
    public function getOauthToken(){return $this->oauthToken;}
    public function getOauthTokenSecret(){return $this->oauthTokenSecret;}
    public function getPpdateInterval(){return $this->updateInterval;}
    public function getTwitterId(){return $this->twitterId;}
    public function getTwitterName(){return $this->twitterName;}
    public function getTwitterScreenName(){return $this->twitterScreenName;}
    public function getTwitterLocation(){return $this->twitterLocation;}
    public function getTwitterProfileImageUrl(){return $this->twitterProfileImageUrl;}
    public function getTwitterUrl(){return $this->twitterUrl;}
    public function getTwitterProtected(){return $this->twitterProtected;}
    public function getTwitterDescription(){return $this->twitterProtected;}
    public function getTwitterFollowersCount(){return $this->twitterFollowersCount;}

// -------- interface Serializable
    /**
     * Serializes properties and returns them as a string which can later on
     * be unserialized.
     *
     * @return string
     */
    public function serialize()
    {
        $data = $this->toArray();

        return serialize($data);
    }

    /**
     * Unserializes <tt>$serialized</tt> and assigns the specific
     * values found to the members in this class.
     *
     * @param string $serialized The serialized representation of a former
     * instance of this class.
     */
    public function unserialize($serialized)
    {
        $str = unserialize($serialized);

         foreach ($str as $member => $value) {
            $this->$member = $value;
        }
    }

// -------- interface Conjoon_BeanContext

    /**
     * Returns a Dto for an instance of this class.
     *
     * @return Conjoon_Modules_Service_Twitter_Account_Dto
     */
    public function getDto()
    {
        $data = $this->toArray();

        $dto = new Conjoon_Modules_Service_Twitter_Account_Dto();
        foreach ($data as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->$key = $value;
            }
        }

        return $dto;
    }

    /**
     * Returns an associative array, which key/value pairs represent
     * the properties stored by this object.
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'id'                     => $this->id,
            'userId'                 => $this->userId,
            'name'                   => $this->name,
            'oauthToken'             => $this->oauthToken,
            'oauthTokenSecret'       => $this->oauthTokenSecret,
            'updateInterval'         => $this->updateInterval,
            'twitterId'              => $this->twitterId,
            'twitterName'            => $this->twitterName,
            'twitterScreenName'      => $this->twitterScreenName,
            'twitterLocation'        => $this->twitterLocation,
            'twitterProfileImageUrl' => $this->twitterProfileImageUrl,
            'twitterUrl'             => $this->twitterUrl,
            'twitterProtected'       => $this->twitterProtected,
            'twitterDescription'     => $this->twitterDescription,
            'twitterFollowersCount'  => $this->twitterFollowersCount
        );
    }

    /**
     * Returns a textual representation of the current object.
     *
     * @return string
     */
    public function __toString()
    {
        $data = $this->toArray();
        return
            '['.
            'id'                     .': '.$this->id.', '.
            'userId'                 .': '.$this->userId.', '.
            'name'                   .': '.$this->name.', '.
            'oauthToken'             .': '.$this->oauthToken.', '.
            'oauthTokenSecret'       .': '.($this->oauthTokenSecret ? '[yes]' : '[no]') .', '.
            'updateInterval'         .': '.$this->updateInterval.', '.
            'twitterId'              .': '.$this->twitterId.', '.
            'twitterName'            .': '.$this->twitterName.', '.
            'twitterScreenName'      .': '.$this->twitterScreenName.', '.
            'twitterLocation'        .': '.$this->twitterLocation.', '.
            'twitterProfileImageUrl' .': '.$this->twitterProfileImageUrl.', '.
            'twitterUrl'             .': '.$this->twitterUrl.', '.
            'twitterProtected'       .': '.$this->twitterProtected.', '.
            'twitterDescription'     .': '.$this->twitterDescription.', '.
            'twitterFollowersCount'  .': '.$this->twitterFollowersCount.';'.
            ']';
    }
}