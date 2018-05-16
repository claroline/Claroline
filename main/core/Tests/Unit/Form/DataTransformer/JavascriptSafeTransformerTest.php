<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\DataTransformer;

class JavascriptSafeTransformerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider inputProvider
     *
     * @param string $input
     * @param string $expectedResult
     */
    public function testReverseTransform($input, $expectedResult)
    {
        $transformer = new JavascriptSafeTransformer();
        $this->assertEquals($expectedResult, $transformer->reverseTransform($input));
    }

    public function inputProvider()
    {
        return [
            ['<p><script>alert("foo")</script></p>', '<p></p>'],
            ['<nav><Script>var z = 123;</SCRIPT></nav>', '<nav></nav>'],
            ['<p> test<script foo=" bar="xyz"> </script ...baz></p>', '<p> test</p>'],
            ['  <body onload="alert(\'baz\')">... ', '  <body >... '],
            ['< li onclick = " var x; "  > test  <li', '< li > test  <li'],
            ['< DIV color="red"  onunload  = "throw new Error()  ">aaa</Div>', '< DIV color="red"  >aaa</Div>'],
            ['<a href="/bar"  onmouseup="alert(789)"  alt="bar">link</a>', '<a href="/bar"  alt="bar">link</a>'],
            ['<p ONKEYup="++i">test</p>', '<p >test</p>'],
            ["<select onselect='a = \"foo\"'>...</select>", '<select >...</select>'],
            ['<html><body color="blue">Correct</body></html>', '<html><body color="blue">Correct</body></html>'],
        ];
    }
}
