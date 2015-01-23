<?php

namespace Icap\PortfolioBundle\Transformer;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Icap\PortfolioBundle\Transformer\XmlToArray;

class XmlToArrayTest extends MockeryTestCase
{
    public function testClassExists()
    {
        $transformer = new XmlToArray();

        $this->assertInstanceOf('\Icap\PortfolioBundle\Transformer\XmlToArray', $transformer);
    }

    public function testTransform()
    {
        $transformer = new XmlToArray();

        $content = <<<CONTENT
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:leap2="http://terms.leapspecs.org/"
      xmlns:categories="http://www.leapspecs.org/2A/categories">
    <leap2:version>http://www.leapspecs.org/2010-07/2A/</leap2:version>
    <id>54be827257316</id>
    <title>54be8272572d8</title>
    <author>
        <name>54be827257361 54be82725739a</name>
    </author>
    <updated>2015-01-20T17:29:38+01:00</updated>
</feed>
CONTENT;

        $expected = array (
            'version' => array(
                'value' => 'http://www.leapspecs.org/2010-07/2A/'
            ),
            'id' =>  array(
                'value' => '54be827257316'
            ),
            'title' =>  array(
                'value' => '54be8272572d8'
            ),
            'author' => array(
                'name' => array(
                    'value' => '54be827257361 54be82725739a'
                ),
            ),
            'updated' =>  array(
                'value' => '2015-01-20T17:29:38+01:00'
            )
        );

        $this->assertEquals($expected, $transformer->transform($content));
    }

    public function testTransformWithEmptyTag()
    {
        $transformer = new XmlToArray();

        $content = <<<CONTENT
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:leap2="http://terms.leapspecs.org/"
      xmlns:categories="http://www.leapspecs.org/2A/categories">
    <content></content>
</feed>
CONTENT;

        $expected = array(
            'content' => array()
        );

        $this->assertEquals($expected, $transformer->transform($content));
    }

    public function testTransformWithAttributes()
    {
        $transformer = new XmlToArray();

        $content = <<<CONTENT
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:leap2="http://terms.leapspecs.org/"
      xmlns:categories="http://www.leapspecs.org/2A/categories">
    <category term="Grouping" scheme="categories:selection_type#"/>
</feed>
CONTENT;

        $expected = array(
            'category' => array(
                'attributes' => array(
                    'term' => 'Grouping',
                    'scheme' => 'categories:selection_type#'
                )
            )
        );

        $this->assertEquals($expected, $transformer->transform($content));
    }

    public function testTransformWithAttributesAndValue()
    {
        $transformer = new XmlToArray();

        $content = <<<CONTENT
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:leap2="http://terms.leapspecs.org/"
      xmlns:categories="http://www.leapspecs.org/2A/categories">
    <content type="text">value</content>
</feed>
CONTENT;

        $expected = array(
            'content' => array(
                'attributes' => array(
                    'type' => 'text',
                ),
                'value' => 'value'
            )
        );

        $this->assertEquals($expected, $transformer->transform($content));
    }

    public function testTransformWithRdfTag()
    {
        $transformer = new XmlToArray();

        $content = <<<CONTENT
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:leap2="http://terms.leapspecs.org/"
      xmlns:categories="http://www.leapspecs.org/2A/categories">
    <rdf:type rdf:resource="leap2:selection"/>
</feed>
CONTENT;

        $expected = array(
            'type' => array(
                'attributes' => array(
                    'resource' => 'leap2:selection'
                )
            )
        );

        $this->assertEquals($expected, $transformer->transform($content));
    }

    public function testTransformWithEntryAndRdfTag()
    {
        $transformer = new XmlToArray();

        $content = <<<CONTENT
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:leap2="http://terms.leapspecs.org/"
      xmlns:categories="http://www.leapspecs.org/2A/categories">
    <leap2:version>http://www.leapspecs.org/2010-07/2A/</leap2:version>
    <id>54be827257316</id>
    <title>54be8272572d8</title>
    <author>
        <name>54be827257361 54be82725739a</name>
    </author>
    <updated>2015-01-20T17:29:38+01:00</updated>

    <entry>
        <title>Mes super badges</title>
        <id>portfolio:badges1</id>
        <updated>2010-10-07T22:30:23+02:00</updated>
        <content></content>

        <rdf:type rdf:resource="leap2:selection"/>
        <category term="Grouping" scheme="categories:selection_type#"/>
    </entry>
</feed>
CONTENT;

        $expected = array (
            'version' => array(
                'value' => 'http://www.leapspecs.org/2010-07/2A/'
            ),
            'id' =>  array(
                'value' => '54be827257316'
            ),
            'title' =>  array(
                'value' => '54be8272572d8'
            ),
            'author' => array(
                'name' => array(
                    'value' => '54be827257361 54be82725739a'
                ),
            ),
            'updated' =>  array(
                'value' => '2015-01-20T17:29:38+01:00'
            ),
            'entry' => array(
                'title'    => array(
                    'value' => 'Mes super badges'
                ),
                'id'       => array(
                    'value' => 'portfolio:badges1'
                ),
                'updated'  => array(
                    'value' => '2010-10-07T22:30:23+02:00'
                ),
                'content'  => array(),
                'type'     => array(
                    'attributes' => array(
                        'resource' => 'leap2:selection'
                    )
                ),
                'category' => array(
                    'attributes' => array(
                        'term' => 'Grouping',
                        'scheme' => 'categories:selection_type#'
                    )
                )
            )
        );

        $this->assertEquals($expected, $transformer->transform($content));
    }
}
