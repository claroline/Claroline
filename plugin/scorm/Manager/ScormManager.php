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

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\ScormBundle\Entity\Scorm12Resource;
use Claroline\ScormBundle\Entity\Scorm12Sco;
use Claroline\ScormBundle\Entity\Scorm12ScoTracking;
use Claroline\ScormBundle\Entity\Scorm2004Resource;
use Claroline\ScormBundle\Entity\Scorm2004Sco;
use Claroline\ScormBundle\Entity\Scorm2004ScoTracking;
use Claroline\ScormBundle\Entity\ScormResource;
use Claroline\ScormBundle\Library\Scorm12;
use Claroline\ScormBundle\Library\Scorm2004;
use Claroline\ScormBundle\Listener\Exception\InvalidScormArchiveException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @DI\Service("claroline.manager.scorm_manager")
 */
class ScormManager
{
    private $om;
    private $container;
    private $scorm12ResourceRepo;
    private $scorm12ScoTrackingRepo;
    private $scorm2004ResourceRepo;
    private $scorm2004ScoTrackingRepo;
    private $scormResourcesPath;
    private $filePath;
    private $libsco12;
    private $libsco2004;
    private $logRepo;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "container"  = @DI\Inject("service_container"),
     *     "libsco12"   = @DI\Inject("claroline.library.scorm_12"),
     *     "libsco2004" = @DI\Inject("claroline.library.scorm_2004")
     * })
     */
    public function __construct(ObjectManager $om, ContainerInterface $container, Scorm12 $libsco12, Scorm2004 $libsco2004)
    {
        $this->om = $om;
        $this->container = $container;
        $this->libsco12 = $libsco12;
        $this->libsco2004 = $libsco2004;
        $this->scorm12ResourceRepo = $om->getRepository('ClarolineScormBundle:Scorm12Resource');
        $this->scorm12ScoTrackingRepo = $om->getRepository('ClarolineScormBundle:Scorm12ScoTracking');
        $this->scorm2004ResourceRepo = $om->getRepository('ClarolineScormBundle:Scorm2004Resource');
        $this->scorm2004ScoTrackingRepo = $om->getRepository('ClarolineScormBundle:Scorm2004ScoTracking');
        $this->scormResourcesPath = $this->container->getParameter('claroline.param.uploads_directory').'/scormresources/';
        $this->filePath = $this->container->getParameter('claroline.param.files_directory').DIRECTORY_SEPARATOR;
        $this->logRepo = $om->getRepository('ClarolineCoreBundle:Log\Log');
    }

    public function persistScorm12(Scorm12Resource $scorm)
    {
        $this->om->persist($scorm);
        $this->om->flush();
    }

    public function persistScorm2004(Scorm2004Resource $scorm)
    {
        $this->om->persist($scorm);
        $this->om->flush();
    }

    public function createScorm($tmpFile, $name, $version)
    {
        //use the workspace as a prefix tor the uploadpath later
        $scormResource = ($version === '1.2') ? new Scorm12Resource() : new Scorm2004Resource();
        $scormResource->setName($name);
        $hashName = $this->container->get('claroline.utilities.misc')->generateGuid().'.zip';
        $scormResource->setHashName($hashName);
        $scos = $this->generateScosFromScormArchive($tmpFile, $version);

        if (count($scos) > 0) {
            $this->om->persist($scormResource);
            $this->persistScos($scormResource, $scos);
        } else {
            throw new InvalidScormArchiveException('no_sco_in_scorm_archive_message');
        }

        $this->unzipScormArchive($tmpFile, $hashName);
        // Move Scorm archive in the files directory
        $tmpFile->move($this->filePath, $hashName);

        return $scormResource;
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

    public function createEmptyScorm12ScoTracking(Scorm12Sco $sco)
    {
        $scoTracking = new Scorm12ScoTracking();
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

    public function createEmptyScorm2004ScoTracking(Scorm2004Sco $sco)
    {
        $scoTracking = new Scorm2004ScoTracking();
        $scoTracking->setSco($sco);
        $scoTracking->setTotalTime('PT0S');
        $scoTracking->setCompletionStatus('unknown');
        $scoTracking->setSuccessStatus('unknown');

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

    public function getScorm12TrackingsByResource(Scorm12Resource $resource)
    {
        return $this->scorm12ScoTrackingRepo->findTrackingsByResource($resource);
    }

    public function getAllScorm12ScoTrackingsByUserAndResource(User $user, Scorm12Resource $resource)
    {
        return $this->scorm12ScoTrackingRepo->findAllTrackingsByUserAndResource($user, $resource);
    }

    public function getScorm12ScoTrackingByUserAndSco(User $user, Scorm12Sco $sco)
    {
        return $this->scorm12ScoTrackingRepo->findOneBy(['user' => $user->getId(), 'sco' => $sco->getId()]);
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

    public function getScorm2004TrackingsByResource(Scorm2004Resource $resource)
    {
        return $this->scorm2004ScoTrackingRepo->findTrackingsByResource($resource);
    }

    public function getAllScorm2004ScoTrackingsByUserAndResource(User $user, Scorm2004Resource $resource)
    {
        return $this->scorm2004ScoTrackingRepo->findAllTrackingsByUserAndResource($user, $resource);
    }

    public function getScorm2004ScoTrackingByUserAndSco(User $user, Scorm2004Sco $sco)
    {
        return $this->scorm2004ScoTrackingRepo->findOneBy(['user' => $user->getId(), 'sco' => $sco->getId()]);
    }

    public function generateScosFromScormArchive(\SplFileInfo $file, $version)
    {
        return $version === '1.2' ? $this->generateScos12FromScormArchive($file) : $this->generateScos2004FromScormArchive($file);
    }

    /***********************************
     * Access to LogRepository methods *
     ***********************************/

    public function getScormTrackingDetails(User $user, ResourceNode $resourceNode, $type = 'scorm12')
    {
        switch ($type) {
            case 'scorm12':
                $action = 'resource-scorm_12-sco_result';
                break;
            case 'scorm2004':
                $action = 'resource-scorm_2004-sco_result';
                break;
            default:
                $action = null;
        }

        return $this->logRepo->findBy(['action' => $action, 'receiver' => $user, 'resourceNode' => $resourceNode], ['dateLog' => 'desc']);
    }

    /**
     * Parses imsmanifest.xml file of a Scorm archive and
     * creates Scos defined in it.
     *
     * @param SplFileInfo $file
     *
     * @return array of Scorm resources
     */
    private function generateScos12FromScormArchive(\SplFileInfo $file)
    {
        $contents = '';
        $zip = new \ZipArchive();

        $zip->open($file);
        $stream = $zip->getStream('imsmanifest.xml');

        while (!feof($stream)) {
            $contents .= fread($stream, 2);
        }
        $dom = new \DOMDocument();

        if (!$dom->loadXML($contents)) {
            throw new InvalidScormArchiveException('cannot_load_imsmanifest_message');
        }

        $scormVersionElements = $dom->getElementsByTagName('schemaversion');

        if ($scormVersionElements->length > 0
            && $scormVersionElements->item(0)->textContent !== '1.2') {
            throw new InvalidScormArchiveException('invalid_scorm_version_12_message');
        }

        $scos = $this->libsco12->parseOrganizationsNode($dom);

        return $scos;
    }

    /**
     * Parses imsmanifest.xml file of a Scorm archive and
     * creates Scos defined in it.
     *
     * @param UploadedFile $file
     *
     * @return array of Scorm resources
     */
    private function generateScos2004FromScormArchive(UploadedFile $file)
    {
        $contents = '';
        $zip = new \ZipArchive();
        $zip->open($file);
        $stream = $zip->getStream('imsmanifest.xml');
        while (!feof($stream)) {
            $contents .= fread($stream, 2);
        }
        $dom = new \DOMDocument();
        if (!$dom->loadXML($contents)) {
            throw new InvalidScormArchiveException('cannot_load_imsmanifest_message');
        }
        $scormVersionElements = $dom->getElementsByTagName('schemaversion');
        if ($scormVersionElements->length > 0
            && $scormVersionElements->item(0)->textContent !== 'CAM 1.3'
            && $scormVersionElements->item(0)->textContent !== '2004 3rd Edition'
            && $scormVersionElements->item(0)->textContent !== '2004 4th Edition') {
            throw new InvalidScormArchiveException('invalid_scorm_version_2004_message');
        }

        $scos = $this->libsco2004->parseOrganizationsNode($dom);

        return $scos;
    }

    /**
     * Associates SCORM resource to SCOs and persists them.
     * As array $scos can also contain an array of scos
     * this method is call recursively when an element is an array.
     *
     * @param Scorm12Resource $scormResource
     * @param array           $scos          Array of Scorm12Sco
     */
    private function persistScos(ScormResource $scormResource, array $scos)
    {
        foreach ($scos as $sco) {
            if (is_array($sco)) {
                $this->persistScos($scormResource, $sco);
            } else {
                $sco->setScormResource($scormResource);
                $this->om->persist($sco);
            }
        }
    }

    /**
     * Unzip a given ZIP file into the web resources directory.
     *
     * @param UploadedFile $file
     * @param $hashName name of the destination directory
     */
    private function unzipScormArchive(\SplFileInfo $file, $hashName)
    {
        $zip = new \ZipArchive();
        $zip->open($file);
        $destinationDir = $this->scormResourcesPath.$hashName;
        if (!file_exists($destinationDir)) {
            mkdir($destinationDir, 0777, true);
        }
        $zip->extractTo($destinationDir);
        $zip->close();
    }
}
