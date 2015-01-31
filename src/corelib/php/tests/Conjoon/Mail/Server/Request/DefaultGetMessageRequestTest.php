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


namespace Conjoon\Mail\Server\Request;

/**
 * @see Request
 */
require_once 'Conjoon/Mail/Server/Request/DefaultGetMessageRequest.php';


/**
 * @category   Conjoon
 * @package    Conjoon_Mail
 * @subpackage UnitTests
 * @group      Conjoon_Mail
 *
 * @author Thorsten Suckow-Homberg <tsuckow@conjoon.org>
 */
class DefaultGetMessageRequestTest extends \PHPUnit_Framework_TestCase {


    protected $messageLocation;

    protected $user;

    protected $request;

    protected function getMessageLocation()
    {
        $folder = new \Conjoon\Mail\Client\Folder\Folder(
            new \Conjoon\Mail\Client\Folder\DefaultFolderPath(
                '["root", "79", "INBOXtttt", "rfwe2", "New folder (7)"]'
            )
        );

        return new \Conjoon\Mail\Client\Message\DefaultMessageLocation(
            $folder, 1
        );
    }

    protected function getUser()
    {
        $userData = array(
            'id'          => 232323,
            'firstName'   => 'wwrwrw',
            'lastName'    => 'ssfsfsf',
            'username'    => 'fsfsf',
            'emailAddress' => 'fssffssf'
        );

        require_once 'Conjoon/Modules/Default/User.php';

        $defaultUser = new \Conjoon_Modules_Default_User();

        $defaultUser->setId($userData['id']);
        $defaultUser->setFirstName($userData['firstName']);
        $defaultUser->setLastName($userData['lastName']);
        $defaultUser->setEmailAddress($userData['emailAddress']);
        $defaultUser->setUserName($userData['username']);

        return new \Conjoon\User\AppUser($defaultUser);
    }

    protected function getRequest()
    {
        return new DefaultGetMessageRequest(array(
            'user'       => $this->user,
            'parameters' => array(
                'messageLocation' => $this->messageLocation
            )
        ));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->user = $this->getUser();

        $this->messageLocation = $this->getMessageLocation();
    }

    /**
     * @expectedException Conjoon\Argument\InvalidArgumentException
     */
    public function testConstructWithException()
    {
        new DefaultGetMessageRequest(array(
            'user'       => $this->user,
            'parameters' => array()
        ));
    }

    /**
     * Ensures everything works as expected
     */
    public function testConstructOk()
    {
        $this->getRequest();
    }

    /**
     * Ensures everything works as expected
     */
    public function testGetProtocolCommand()
    {
        $this->assertSame(
            "getMessage",
            $this->getRequest()->getProtocolCommand()
        );
    }

    /**
     * Ensures everything works as expected
     */
    public function testGetUser()
    {
        $this->assertSame(
            $this->user,
            $this->getRequest()->getUser()
        );
    }

    /**
     * Ensures everything works as expected
     */
    public function testGetMessageLocation()
    {
        $this->assertSame(
            $this->messageLocation,
            $this->getRequest()->getParameter('messageLocation')
        );
    }

    /**
     * Ensures everything works as expected
     */
    public function testGetParameters()
    {
        $params = $this->getRequest()->getParameters();

        $this->assertSame(
            $this->messageLocation,
            $params['messageLocation']
        );
    }
}
