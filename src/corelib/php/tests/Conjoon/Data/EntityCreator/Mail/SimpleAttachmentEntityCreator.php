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

namespace Conjoon\Data\EntityCreator\Mail;

/**
 * @see Conjoon\Data\EntityCreator\Mail\AttachmentEntityCreator
 */
require_once 'Conjoon/Data/EntityCreator/Mail/AttachmentEntityCreator.php';

/**
 * @package    Conjoon/Tests
 *
 * @author Thorsten Suckow-Homberg <tsuckow@conjoon.org>
 */
class SimpleAttachmentEntityCreator implements AttachmentEntityCreator {

    public function createListFrom(\Conjoon\Mail\Message\RawMessage $message)
    {
        return array(
            new \Conjoon\Data\Entity\Mail\DefaultAttachmentEntity(),
            new \Conjoon\Data\Entity\Mail\DefaultAttachmentEntity()
        );
    }

    public function createFrom(array $options)
    {
        return new \Conjoon\Data\Entity\Mail\ImapMessageEntity();
    }

}
