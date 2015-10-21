<?php

require_once dirname(dirname(dirname(__FILE__))) . '/XMPPHP/Log.php';

class XMPPHP_LogTest extends PHPUnit_Framework_TestCase
{
    public function testPrintoutNoOutput()
    {
        $log = new XMPPHP_Log();
        
        $msg = 'I am a test log message';
        
        ob_start();
        $log->log('test');
        $result = ob_get_clean();
        
        $this->assertEquals('', $result);
    }
    
    public function testPrintoutOutput()
    {
        $log = new XMPPHP_Log(true);
        
        $msg = 'I am a test log message';
        
        ob_start();
        $log->log($msg);
        $result = ob_get_clean();
        
        $this->assertContains($msg, $result);
    }

    public function testPrintoutNoOutputWithDefaultLevel()
    {
        $log = new XMPPHP_Log(true, XMPPHP_Log::LEVEL_ERROR);
        
        $msg = 'I am a test log message';
        
        ob_start();
        $log->log($msg);
        $result = ob_get_clean();
        
        $this->assertSame('', $result);
    }

    public function testPrintoutOutputWithDefaultLevel()
    {
        $log = new XMPPHP_Log(true, XMPPHP_Log::LEVEL_INFO);
        
        $msg = 'I am a test log message';
        
        ob_start();
        $log->log($msg);
        $result = ob_get_clean();
        
        $this->assertContains($msg, $result);
    }

    public function testPrintoutNoOutputWithCustomLevel()
    {
        $log = new XMPPHP_Log(true, XMPPHP_Log::LEVEL_INFO);
        
        $msg = 'I am a test log message';
        
        ob_start();
        $log->log($msg, XMPPHP_Log::LEVEL_DEBUG);
        $result = ob_get_clean();
        
        $this->assertSame('', $result);
    }
    
    public function testPrintoutOutputWithCustomLevel()
    {
        $log = new XMPPHP_Log(true, XMPPHP_Log::LEVEL_INFO);
        
        $msg = 'I am a test log message';
        
        ob_start();
        $log->log($msg, XMPPHP_Log::LEVEL_INFO);
        $result = ob_get_clean();
        
        $this->assertContains($msg, $result);
    }

    public function testExplicitPrintout()
    {
        $log = new XMPPHP_Log(false);
        
        $msg = 'I am a test log message';
        
        ob_start();
        $log->log($msg);
        $result = ob_get_clean();
        
        $this->assertSame('', $result);
    }

    public function testExplicitPrintoutResult()
    {
        $log = new XMPPHP_Log(false);
        
        $msg = 'I am a test log message';
        
        ob_start();
        $log->log($msg);
        $result = ob_get_clean();
        
        $this->assertSame('', $result);
        
        ob_start();
        $log->printout();
        $result = ob_get_clean();
        
        $this->assertContains($msg, $result);
    }

    public function testExplicitPrintoutClear()
    {
        $log = new XMPPHP_Log(false);
        
        $msg = 'I am a test log message';
        
        ob_start();
        $log->log($msg);
        $result = ob_get_clean();
        
        $this->assertSame('', $result);
        
        ob_start();
        $log->printout();
        $result = ob_get_clean();
        
        $this->assertContains($msg, $result);

        ob_start();
        $log->printout();
        $result = ob_get_clean();
        
        $this->assertSame('', $result);
    }
    
    public function testExplicitPrintoutLevel()
    {
        $log = new XMPPHP_Log(false, XMPPHP_Log::LEVEL_ERROR);
        
        $msg = 'I am a test log message';
        
        ob_start();
        $log->log($msg);
        $result = ob_get_clean();
        
        $this->assertSame('', $result);
        
        ob_start();
        $log->printout(true, XMPPHP_Log::LEVEL_INFO);
        $result = ob_get_clean();
        
        $this->assertSame('', $result);
    }

    
}
