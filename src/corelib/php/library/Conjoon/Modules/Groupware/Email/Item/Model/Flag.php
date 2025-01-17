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
 * @see Conjoon_Db_Table
 */
require_once 'Conjoon/Db/Table.php';

/**
 * Table data gateway. Models the table <tt>groupware_email_items_flags</tt>.
 *
 * @uses Conjoon_Db_Table
 * @package Conjoon_Groupware_Email
 * @subpackage Model
 * @category Model
 *
 * @author Thorsten Suckow-Homberg <tsuckow@conjoon.org>
 */
class Conjoon_Modules_Groupware_Email_Item_Model_Flag extends Conjoon_Db_Table {

    /**
     * The name of the table in the underlying datastore this
     * class represents.
     * @var string
     */
    protected $_name = 'groupware_email_items_flags';

    /**
     * The name of the column that denotes the primary key for this table
     * @var string
     */
    protected $_primary = array(
        'groupware_email_items_id',
        'user_id'
    );


    /**
     * Returns an aray keyed by the passed ids and value set to either tru or false
     * if the ccorresponding item has an entry with is_deleted= 0.
     *
     * @param integer|array $id
     *
     * @return array
     */
    public function areItemsFlaggedAsDeleted($id)
    {
        $tmpIds = (array)$id;
        $ids    = array();

        for ($i = 0, $len = count($tmpIds); $i < $len; $i++) {
            $id = (int)$tmpIds[$i];
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        if (count($ids) == 0) {
            return array();
        }

        $select = $this->select()->from($this, array(
                    'groupware_email_items_id'
                  ))
                  ->where('groupware_email_items_id IN (' . implode(',', $ids) . ')')
                  ->where('is_deleted=0')
                  ->group('groupware_email_items_id');

        $rows = $this->fetchAll($select);
        // rows contains ids which are not deleted!
        $returnArray = array_fill_keys($ids, true);

        foreach ($rows as $row) {
            $returnArray[$row->groupware_email_items_id] = false;
        }

        return $returnArray;

    }


    /**
     * Marks a specified item for the specified user as either "deleted"
     *
     * @param integer|array
     *
     * @return integer the number of rows updated
     */
    public function flagItemsAsDeleted($groupwareEmailItemsId, $userId)
    {
        $tmpIds = (array)$groupwareEmailItemsId;
        $ids    = array();

        for ($i = 0, $len = count($tmpIds); $i < $len; $i++) {
            $id = (int)$tmpIds[$i];
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        $userId = (int)$userId;

        if (count($ids) == 0 || $userId <= 0) {
            return 0;
        }

        $data = array('is_deleted' => 1);
        $adapter = $this->getAdapter();
        return $this->update($data, array(
            'groupware_email_items_id IN (' . implode(',', $ids) . ')',
            $adapter->quoteInto('user_id = ?', $userId, 'INTEGER')
        ));
    }


    /**
     * Marks a specified item for the specified user as either "read" or "unread"
     *
     * @return integer 1, if the data has been updated, otherwise 0
     */
    public function flagItemAsRead($groupwareEmailItemsId, $userId, $isRead)
    {
        $this->flagItemImpl($groupwareEmailItemsId, $userId, $isRead, 'is_read');
    }

    /**
     * Marks a specified item for the specified user as either "spam" or "no spam"
     *
     * @return integer 1, if the data has been updated, otherwise 0
     */
    public function flagItemAsSpam($groupwareEmailItemsId, $userId, $isSpam)
    {
        $this->flagItemImpl($groupwareEmailItemsId, $userId, $isSpam, 'is_spam');
    }

    /**
     * Flag processing implementation.
     *
     */
    protected function flagItemImpl($groupwareEmailItemsId, $userId, $isSet, $type)
    {
        $groupwareEmailItemsId = (int)$groupwareEmailItemsId;
        $userId                = (int)$userId;

        if ($groupwareEmailItemsId <= 0 || $userId <= 0) {
            return 0;
        }

        $data = array($type => (int)((bool)$isSet));

        if (!$this->fetchRow($this->select()
            ->where('groupware_email_items_id = ?', $groupwareEmailItemsId)
            ->where('user_id = ?',                  $userId))) {
            $this->insert(array(
                'groupware_email_items_id' => $groupwareEmailItemsId,
                'user_id'                  => $userId,
                $type                      => (int) ((bool)$isSet)
            ));

            return 1;
        }

        $adapter = $this->getAdapter();
        $this->update($data, array(
            $adapter->quoteInto('groupware_email_items_id = ?', $groupwareEmailItemsId, 'INTEGER'),
            $adapter->quoteInto('user_id = ?', $userId, 'INTEGER')
        ));
    }


}