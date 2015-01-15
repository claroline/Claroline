<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CursusBundle\Entity\CursusDisplayedWord;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * @DI\Service("claroline.manager.cursus_manager")
 */
class CursusManager
{
    private $om;
    private $translator;
    private $cursusWordRepo;
    
    /**
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "translator"      = @DI\Inject("translator")
     * })
     */
    public function __construct(
        ObjectManager $om,
        Translator $translator
    )
    {
        $this->om = $om;
        $this->translator = $translator;
        $this->cursusWordRepo =
            $om->getRepository('ClarolineCursusBundle:CursusDisplayedWord');
    }
    
    public function persistCursusDisplayedWord(CursusDisplayedWord $word)
    {
        $this->om->persist($word);
        $this->om->flush();
    }
    
    public function getDisplayedWord($word)
    {
        $cursusDisplayedWord = $this->cursusWordRepo->findOneByWord($word);
        
        if (is_null($cursusDisplayedWord)) {
            $result = $this->translator->trans($word, array(), 'cursus');
        } else {
            $displayedWord = $cursusDisplayedWord->getDisplayedWord();
            $result = empty($displayedWord) ?
                $this->translator->trans($word, array(), 'cursus'):
                $displayedWord;
        }
        
        return $result;
    }
    
    private function initializeDisplayedWord($word)
    {
        $toDisplay = $this->translator->trans($word, array(), 'cursus');
        $displayedWord = new CursusDisplayedWord();
        $displayedWord->setWord($word);
        $displayedWord->setDisplayedWord($toDisplay);
        $this->om->persist($displayedWord);
        $this->om->flush();
        
        return $toDisplay;
    }    
    

    /***************************************************
     * Access to CursusDisplayedWordRepository methods *
     ***************************************************/
    
    public function getOneDisplayedWordByWord($word)
    {
        return $this->cursusWordRepo->findOneByWord($word);
    }
}