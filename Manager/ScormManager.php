<?php


/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\ScormBundle\Entity\Scorm12Sco;
use Claroline\ScormBundle\Entity\Scorm12ScoTracking;
use Claroline\ScormBundle\Entity\Scorm2004Sco;
use Claroline\ScormBundle\Entity\Scorm2004ScoTracking;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.scorm_manager")
 */
class ScormManager
{
    private $om;
    private $scorm12ResourceRepo;
    private $scorm12ScoTrackingRepo;
    private $scorm2004ResourceRepo;
    private $scorm2004ScoTrackingRepo;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->scorm12ResourceRepo =
            $om->getRepository('ClarolineScormBundle:Scorm12Resource');
        $this->scorm12ScoTrackingRepo =
            $om->getRepository('ClarolineScormBundle:Scorm12ScoTracking');
        $this->scorm2004ResourceRepo =
            $om->getRepository('ClarolineScormBundle:Scorm2004Resource');
        $this->scorm2004ScoTrackingRepo =
            $om->getRepository('ClarolineScormBundle:Scorm2004ScoTracking');
    }

    public function createScorm12ScoTracking(User $user, Scorm12Sco $sco)
    {
        $scoTracking = new Scorm12ScoTracking();
        $scoTracking->setUser($user);
        $scoTracking->setSco($sco);
        $scoTracking->setLessonStatus('not attempted');
        $scoTracking->setSuspendData('');
        $scoTracking->setEntry('ab-initio');
        $scoTracking->setLessonLocation('');
        $scoTracking->setCredit('no-credit');
        $scoTracking->setTotalTime(0);
        $scoTracking->setSessionTime(0);
        $scoTracking->setLessonMode('normal');
        $scoTracking->setExitMode('');
        $scoTracking->setBestLessonStatus('not attempted');

        if (is_null($sco->getPrerequisites())) {
            $scoTracking->setIsLocked(false);
        } else {
            $scoTracking->setIsLocked(true);
        }
        $this->om->persist($scoTracking);
        $this->om->flush();

        return $scoTracking;
    }

    public function updateScorm12ScoTracking(Scorm12ScoTracking $scoTracking)
    {
        $this->om->persist($scoTracking);
        $this->om->flush();
    }

    public function createScorm2004ScoTracking(User $user, Scorm2004Sco $sco)
    {
        $scoTracking = new Scorm2004ScoTracking();
        $scoTracking->setUser($user);
        $scoTracking->setSco($sco);
        $scoTracking->setTotalTime('PT0S');
        $scoTracking->setCompletionStatus('unknown');
        $scoTracking->setSuccessStatus('unknown');
        $this->om->persist($scoTracking);
        $this->om->flush();

        return $scoTracking;
    }

    public function updateScorm2004ScoTracking(Scorm2004ScoTracking $scoTracking)
    {
        $this->om->persist($scoTracking);
        $this->om->flush();
    }


    /***********************************************
     * Access to Scorm12ResourceRepository methods *
     ***********************************************/

    public function getNbScorm12WithHashName($hashName)
    {
        return $this->scorm12ResourceRepo->getNbScormWithHashName($hashName);
    }


    /**************************************************
     * Access to Scorm12ScoTrackingRepository methods *
     **************************************************/

    public function getScorm12ScoTrackingByUserAndSco(
        User $user,
        Scorm12Sco $sco
    )
    {
        return $this->scorm12ScoTrackingRepo->findOneBy(
            array('user' => $user->getId(), 'sco' => $sco->getId())
        );
    }


    /*************************************************
     * Access to Scorm2004ResourceRepository methods *
     *************************************************/

    public function getNbScorm2004WithHashName($hashName)
    {
        return $this->scorm2004ResourceRepo->getNbScormWithHashName($hashName);
    }


    /****************************************************
     * Access to Scorm2004ScoTrackingRepository methods *
     ****************************************************/

    public function getScorm2004ScoTrackingByUserAndSco(
        User $user,
        Scorm2004Sco $sco
    )
    {
        return $this->scorm2004ScoTrackingRepo->findOneBy(
            array('user' => $user->getId(), 'sco' => $sco->getId())
        );
    }
}
