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

use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Cursus;
use Claroline\CursusBundle\Entity\CursusDisplayedWord;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * @DI\Service("claroline.manager.cursus_manager")
 */
class CursusManager
{
    private $om;
    private $pagerFactory;
    private $translator;
    private $courseRepo;
    private $cursusRepo;
    private $cursusWordRepo;
    
    /**
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory"    = @DI\Inject("claroline.pager.pager_factory"),
     *     "translator"      = @DI\Inject("translator")
     * })
     */
    public function __construct(
        ObjectManager $om,
        PagerFactory $pagerFactory,
        Translator $translator
    )
    {
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;
        $this->translator = $translator;
        $this->courseRepo =
            $om->getRepository('ClarolineCursusBundle:Course');
        $this->cursusRepo =
            $om->getRepository('ClarolineCursusBundle:Cursus');
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

    public function persistCursus(Cursus $cursus)
    {
        $this->om->persist($cursus);
        $this->om->flush();
    }

    public function deleteCursus(Cursus $cursus)
    {
        $this->om->remove($cursus);
        $this->om->flush();
    }

    public function persistCourse(Course $course)
    {
        $this->om->persist($course);
        $this->om->flush();
    }

    public function deleteCourse(Course $course)
    {
        $this->om->remove($course);
        $this->om->flush();
    }
    

    /***************************************************
     * Access to CursusDisplayedWordRepository methods *
     ***************************************************/
    
    public function getOneDisplayedWordByWord($word)
    {
        return $this->cursusWordRepo->findOneByWord($word);
    }


    /**************************************
     * Access to CursusRepository methods *
     **************************************/

    public function getAllRootCursus($executeQuery = true)
    {
        return $this->cursusRepo->findAllRootCursus($executeQuery);
    }

    public function getLastRootCursusOrder($executeQuery = true)
    {
        return $this->cursusRepo->findLastRootCursusOrder($executeQuery);
    }

    public function getLastCursusOrderByParent(Cursus $cursus, $executeQuery = true)
    {
        return $this->cursusRepo->findLastCursusOrderByParent($cursus, $executeQuery);
    }

    public function getHierarchyByCursus(Cursus $cursus, $executeQuery = true)
    {
        return $this->cursusRepo->findHierarchyByCursus($cursus, $executeQuery);
    }


    /**************************************
     * Access to CourseRepository methods *
     **************************************/

    public function getAllCourses(
        $orderedBy = 'title',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    )
    {
        $courses = $this->courseRepo->findAllCourses(
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($courses, $page, $max) :
            $this->pagerFactory->createPager($courses, $page, $max);
    }

    public function getSearchedCourses(
        $search = '',
        $orderedBy = 'title',
        $order = 'ASC',
        $page = 1,
        $max = 50,
        $executeQuery = true
    )
    {
        $courses = $this->courseRepo->findSearchedCourses(
            $search,
            $orderedBy,
            $order,
            $executeQuery
        );

        return $executeQuery ?
            $this->pagerFactory->createPagerFromArray($courses, $page, $max) :
            $this->pagerFactory->createPager($courses, $page, $max);
    }
}