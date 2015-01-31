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


namespace Conjoon\Mail\Client\Message;

use Conjoon\Argument\ArgumentCheck;

/**
 * @see \Conjoon\Mail\Client\Message\AttachmentLocation
 */
require_once 'Conjoon/Mail/Client/Message/AttachmentLocation.php';

/**
 * @see \Conjoon\Argument\ArgumentCheck
 */
require_once 'Conjoon/Argument/ArgumentCheck.php';

/**
 * Default implementation for a MessageLocation
 *
 * @category   Conjoon_Mail
 * @package    Conjoon_Mail_Client
 *
 * @author Thorsten Suckow-Homberg <tsuckow@conjoon.org>
 */
class DefaultAttachmentLocation implements AttachmentLocation {

    /**
     * @var Conjoon\Mail\Client\Message\MessageLocation
     */
    protected $messageLocation;

    /**
     * @var string
     */
    protected $id;

    /**
     * Creates a new instance of this class.
     *
     * @param \Conjoon\Mail\Client\Message\MessageLocation $messageLocation
     * @param mixed $id
     *
     * @throws \Conjoon\Argument\InvalidArgumentException
     */
    public function __construct(MessageLocation $messageLocation, $id)
    {
        $data = array('id' => $id);

        ArgumentCheck::check(array(
            'id' => array(
                'type'       => 'string',
                'allowEmpty' => false
        )), $data);

        $id = $data['id'];

        $this->id = $id;

        $this->messageLocation = $messageLocation;
    }

    /**
     * @inheritdoc
     */
    public function getMessageLocation()
    {
        return $this->messageLocation;
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier()
    {
        return $this->id;
    }

}

