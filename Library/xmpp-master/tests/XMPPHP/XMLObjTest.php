<?php

require_once dirname(dirname(dirname(__FILE__))) . '/XMPPHP/XMLObj.php';

class XMPPHP_XMLObjTest extends PHPUnit_Framework_TestCase
{
    public function testToStringNameNamespace()
    {
        $xmlobj = new XMPPHP_XMLObj('testname', 'testNameSpace');
        
        $expected = "<testname xmlns='testNameSpace' ></testname>";
        
        $result = $xmlobj->toString();
        
        $this->assertSame($expected, $result);
    }

    public function testToStringNameNamespaceAttr()
    {
        $xmlobj = new XMPPHP_XMLObj('testName', 'testNameSpace', array('attr1'=>'valA', 'attr2'=>'valB'));
        
        $expected = "<testname xmlns='testNameSpace' attr1='valA' attr2='valB' ></testname>";
        
        $result = $xmlobj->toString();
        
        $this->assertSame($expected, $result);
    }
    
    public function testToStringNameNamespaceData()
    {
        $xmlobj = new XMPPHP_XMLObj('testName', 'testNameSpace', array(), 'I am test data');
        
        $expected = "<testname xmlns='testNameSpace' >I am test data</testname>";
        
        $result = $xmlobj->toString();
        
        $this->assertSame($expected, $result);
    }
    
    public function testToStringNameNamespaceSub()
    {
        $xmlobj = new XMPPHP_XMLObj('testName', 'testNameSpace');
        $sub1 = new XMPPHP_XMLObj('subName', 'subNameSpace');
        $xmlobj->subs = array($sub1);
        
        $expected = "<testname xmlns='testNameSpace' ><subname xmlns='subNameSpace' ></subname></testname>";
        
        $result = $xmlobj->toString();
        
        $this->assertSame($expected, $result);
    }
    
}
