<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

class TextTest extends TransactionalTestCase
{
    /** @var Doctrine\ORM\EntityManager */
    private $em;
    
    protected function setUp()
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
            ->getRepository('Claroline\CoreBundle\Entity\Text')
            ->findOneBy(array('type' => 'text/html', 'content' => '<p>Test content</p>'));
        
        $this->assertEquals($text, $retreivedText);
    }
}