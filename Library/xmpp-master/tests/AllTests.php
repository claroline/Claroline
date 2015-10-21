<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

require_once 'XMPPHP/LogTest.php';
require_once 'XMPPHP/XMLObjTest.php';
require_once 'XMPPHP/XMPPTest.php';

class AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();

        $suite->addTestSuite('XMPPHP_LogTest');
        $suite->addTestSuite('XMPPHP_XMLObjTest');
        $suite->addTestSuite('XMPPHP_XMPPTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
    AllTests::main();
}
