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

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Conjoon_Mail_AllTests::main');
}


require_once 'Conjoon/Mail/SentTest.php';
require_once 'Conjoon/Mail/MessageTest.php';
require_once 'Conjoon/Mail/ExceptionTest.php';
require_once 'Conjoon/Mail/Storage/AllTests.php';
require_once 'Conjoon/Mail/Protocol/AllTests.php';
require_once 'Conjoon/Mail/Service/AllTests.php';
require_once 'Conjoon/Mail/Facade/AllTests.php';
require_once 'Conjoon/Mail/Client/AllTests.php';

/**
 * @category   Conjoon
 * @package    Conjoon_Mail
 * @subpackage UnitTests
 *
 * @author Thorsten Suckow-Homberg <tsuckow@conjoon.org>
 */
class Conjoon_Mail_AllTests
{
    /**
     * Runs this test suite
     *
     * @return void
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    /**
     * Creates and returns this test suite
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('conjoon - Conjoon_Mail');

        $suite->addTestSuite('Conjoon_Mail_Exception');
        $suite->addTestSuite('Conjoon_Mail_Message');
        $suite->addTestSuite('Conjoon_Mail_Sent');

        $suite->addTest(Conjoon_Mail_Storage_AllTests::suite());
        $suite->addTest(Conjoon_Mail_Protocol_AllTests::suite());
        $suite->addTest(Conjoon_Mail_Service_AllTests::suite());
        $suite->addTest(Conjoon_Mail_Facade_AllTests::suite());
        $suite->addTest(Conjoon_Mail_Client_AllTests::suite());

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Conjoon_Mail_AllTests::main') {
    Conjoon_Mail_AllTests::main();
}
