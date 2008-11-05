<?php
/**
 * intrabuild
 * (c) 2002-2008 siteartwork.de/MindPatterns
 * license@siteartwork.de
 *
 * $Author: T. Suckow $
 * $Id: ItemFilter.php 2 2008-06-21 10:38:49Z T. Suckow $
 * $Date: 2008-06-21 12:38:49 +0200 (Sa, 21 Jun 2008) $
 * $Revision: 2 $
 * $LastChangedDate: 2008-06-21 12:38:49 +0200 (Sa, 21 Jun 2008) $
 * $LastChangedBy: T. Suckow $
 * $URL: file:///F:/svn_repository/intrabuild/trunk/src/corelib/php/library/Intrabuild/Modules/Groupware/Feeds/ItemFilter.php $
 */

/**
 * @see Intrabuild_Filter_Input
 */
require_once 'Intrabuild/Filter/Input.php';

/**
 * @see Intrabuild_Filter_FormBoolToInt
 */
require_once 'Intrabuild/Filter/FormBoolToInt.php';

/**
 * @see Intrabuild_Filter_Raw
 */
require_once 'Intrabuild/Filter/Raw.php';

/**
 * @see Intrabuild_Filter_ShortenString
 */
require_once 'Intrabuild/Filter/ShortenString.php';

/**
 * @see Zend_Filter_HtmlEntities
 */
require_once 'Zend/Filter/HtmlEntities.php';

/**
 * @see Intrabuild_Filter_HtmlEntityDecode
 */
require_once 'Intrabuild/Filter/HtmlEntityDecode.php';

/**
 * An input-filter class defining all validators and filters needed when
 * processing input data for mutating or creating feed items.
 *
 * @uses Intrabuild_Filter_Input
 * @package    Intrabuild_Filter_Input
 * @category   Filter
 *
 * @author Thorsten Suckow-Homberg <ts@siteartwork.de>
 */
class Intrabuild_Modules_Groupware_Feeds_Item_Filter_Item extends Intrabuild_Filter_Input {

    const CONTEXT_READ = 'flag_item';

    const CONTEXT_ITEM_RESPONSE = 'item_response';

    protected $_presence = array(
        'delete' =>
            array(
                'id'
            )
        ,
        'flag_item' =>
            array(
                'id',
                'isRead'
            ),
        'update' =>
            array(
                'removeold',
                'timeout'
            ),
        self::CONTEXT_RESPONSE => array(
            'id',
            'groupwareFeedsAccountsId',
            'name',
            'title',
            'author',
            'description',
            'pubDate',
            'link',
            'isRead'
        ),
        self::CONTEXT_ITEM_RESPONSE => array(
            'id',
            'groupwareFeedsAccountsId',
            'name',
            'content',
            'title',
            'author',
            'description',
            'pubDate',
            'link',
            'isRead'
        ),
        'create' =>
            array(
                'title',
                'description',
                'content',
                'pubDate',
                'link',
                'guid',
                'author',
                'savedTimestamp',
                'groupwareFeedsAccountsId'
        )
    );

    protected $_filters = array(
        'id' => array(
            'Int'
         ),
         'name' => array(
            'StringTrim'
         ),
         'groupwareFeedsAccountsId' => array(
            'Int'
         ),
        'title' => array(
            'StringTrim'
         ),
         'guid' => array(
            'StringTrim'
         ),
         'author' => array(
            'StringTrim'
         ),
        'description' => array(
            'StringTrim'
         ),
         'content' => array(
            'StringTrim'
         ),
        'pubDate' => array(
            'StringTrim'
         ),
        'link' => array(
            'StringTrim'
        ),
        'savedTimestamp' => array(
            'Int'
        ),
        'isRead' => array(
            'Int'
        ),
        'removeold' => array(),
        'timeout'   => 'Int',
    );

    protected $_validators = array(
        'id' => array(
            'allowEmpty' => false,
            array('GreaterThan', 0)
         ),
         'groupwareFeedsAccountsId' => array(
            'allowEmpty' => false,
            array('GreaterThan', 0)
         ),
        'title' => array(
            'allowEmpty' => true
         ),
         'name' => array(
            'allowEmpty' => true,
            'default' => ''
         ),
        'author' => array(
            'allowEmpty' => true,
            'default' => ''
         ),
        'description' => array(
            'allowEmpty' => true,
            'default' => ''
         ),
        'pubDate' => array(
            'allowEmpty' => false
         ),
         'content' => array(
            'allowEmpty' => true,
            'default' => ''
         ),
         'guid' => array(
            'allowEmpty' => false
         ),
        'link' => array(
            'allowEmpty' => false
        ),
        'savedTimestamp' => array(
            'allowEmpty' => true,
            array('GreaterThan', 0)
        ),
        'isRead' => array(
           'allowEmpty' => true,
           'default'    => 0
        ),
        'removeold' => array(
            'allowEmpty' => true,
            'default'    => 0
        ),
        'timeout' => array(
            'allowEmpty' => true,
            array('GreaterThan', 0),
            'default'    => 30000
        )
    );

    protected function _init()
    {
        if ($this->_context == self::CONTEXT_RESPONSE || $this->_context == self::CONTEXT_ITEM_RESPONSE) {

            $this->_filters['title'] = new Zend_Filter_HtmlEntities(ENT_COMPAT, 'UTF-8');

            $this->_filters['description'] = array(
                array('StripTags',
                    // allow all except img, a, object, script, embed etc.
                    array(
                        'p','div','span','ul','li','table','tr','td','blockquote',
                        'strong','b','i','u','sup','sub','tt','h1','h2','h3','h4',
                        'h5','h6','small','big','br','nobr','center','ol', 'font',
                        'pre'
                    ),
                    array(
                        'style', 'class','id', 'href', 'border', 'cellspacing', 'cellpadding'
                    )
                ),
                array(
                    'PregReplace',
                    '/href="javascript:/',
                    'href="/index/javascript/'
                ),
                array(
                    'PregReplace',
                    "/href='javascript:/",
                    "href='/index/javascript/"
                ),
                'StringTrim',
                new Intrabuild_Filter_ShortenString(128, '...')
            );

            if ($this->_context == self::CONTEXT_ITEM_RESPONSE) {
                $this->_filters['content'] = array(
                    array('StripTags',
                        // allow all except img, object, script, embed etc.
                        array(
                            'p','div','span','ul','li','table','tr','td','blockquote',
                            'strong','b','i','u','sup','sub','tt','h1','h2','h3','h4',
                            'h5','h6','small','big','br','nobr','center','ol','a','font',
                            'pre'
                        ),
                        array(
                            'style', 'class','id', 'href', 'border', 'cellspacing', 'cellpadding'
                        )
                    ),
                    array(
                        'PregReplace',
                        '/href="javascript:/',
                        'href="/index/javascript/'
                    ),
                    array(
                        'PregReplace',
                        "/href='javascript:/",
                        "href='/index/javascript/"
                    ),
                    'StringTrim'
                );
            }
        }

        $this->_defaultEscapeFilter = new Intrabuild_Filter_Raw();


        $this->_filters['removeold'] = new Intrabuild_Filter_FormBoolToInt();
        $this->_validators['savedTimestamp']['default'] = time();
    }


}