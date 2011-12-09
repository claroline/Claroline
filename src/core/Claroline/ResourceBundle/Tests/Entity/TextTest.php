<?php

namespace Claroline\ResourceBundle\Entity;

use Claroline\CommonBundle\Library\Testing\TransactionalTestCase;

class TextTest extends TransactionalTestCase
{
    /** @var Doctrine\ORM\EntityManager */
    private $em;
    
    public function setUp()
    {
        parent::setUp();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }
    
    public function testTextEntityCanBePersistedAndRetreived()
    {
        $text = new Text();
        $text->setType('text/html');
        $text->setContent('<p>Test content</p>');
        
        $this->em->persist($text);
        $this->em->flush();
        
        $retreivedText = $this->em
            ->getRepository('Claroline\ResourceBundle\Entity\Text')
            ->findOneBy(array('type' => 'text/html', 'content' => '<p>Test content</p>'));
        
        $this->assertEquals($text, $retreivedText);
    }
}