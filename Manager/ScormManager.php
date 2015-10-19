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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\ScormBundle\Entity\Scorm12Resource;
use Claroline\ScormBundle\Entity\Scorm12Sco;
use Claroline\ScormBundle\Entity\Scorm12ScoTracking;
use Claroline\ScormBundle\Entity\Scorm2004Resource;
use Claroline\ScormBundle\Entity\Scorm2004Sco;
use Claroline\ScormBundle\Entity\Scorm2004ScoTracking;
use Symfony\Component\DependencyInjection\ContainerInterface;
use JMS\DiExtraBundle\Annotation as DI;

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

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"        = @DI\Inject("claroline.persistence.object_manager"),
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(ObjectManager $om, ContainerInterface $container)
    {
        $this->om = $om;
        $this->container = $container;
        $this->scorm12ResourceRepo =
            $om->getRepository('ClarolineScormBundle:Scorm12Resource');
        $this->scorm12ScoTrackingRepo =
            $om->getRepository('ClarolineScormBundle:Scorm12ScoTracking');
        $this->scorm2004ResourceRepo =
            $om->getRepository('ClarolineScormBundle:Scorm2004Resource');
        $this->scorm2004ScoTrackingRepo =
            $om->getRepository('ClarolineScormBundle:Scorm2004ScoTracking');
        $this->scormResourcesPath = $this->container
            ->getParameter('claroline.param.uploads_directory') . '/scormresources/';
        $this->filePath = $this->container
            ->getParameter('claroline.param.files_directory') . DIRECTORY_SEPARATOR;
    }

    public function createScorm12($tmpFile, $name, Workspace $workspace)
    {
        $prefix = 'WORKSPACE_' . $workspace->getId();
        $scormResource = new Scorm12Resource();
        $scormResource->setName($name);
        $hashName = $this->container->get('claroline.utilities.misc')
                ->generateGuid() . '.zip';
        $scormResource->setHashName($prefix . DIRECTORY_SEPARATOR . $hashName);
        $scos = $this->generateScosFromScormArchive($tmpFile);

        if (count($scos) > 0) {
            $this->om->persist($scormResource);
            $this->persistScos($scormResource, $scos);
        } else {
            throw new InvalidScormArchiveException('no_sco_in_scorm_archive_message');
        }
        $this->unzipScormArchive($tmpFile, $hashName, $prefix);
        // Move Scorm archive in the files directory
        $tmpFile->move($this->filePath . DIRECTORY_SEPARATOR . $prefix, $hashName);

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

    public function getAllScorm12ScoTrackingsByUserAndResource(
        User $user,
        Scorm12Resource $resource
    )
    {
        return $this->scorm12ScoTrackingRepo
            ->findAllTrackingsByUserAndResource($user, $resource);
    }

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

    public function getAllScorm2004ScoTrackingsByUserAndResource(
        User $user,
        Scorm2004Resource $resource
    )
    {
        return $this->scorm2004ScoTrackingRepo
            ->findAllTrackingsByUserAndResource($user, $resource);
    }

    public function getScorm2004ScoTrackingByUserAndSco(
        User $user,
        Scorm2004Sco $sco
    )
    {
        return $this->scorm2004ScoTrackingRepo->findOneBy(
            array('user' => $user->getId(), 'sco' => $sco->getId())
        );
    }


    /**
     * Parses imsmanifest.xml file of a Scorm archive and
     * creates Scos defined in it.
     *
     * @param SplFileInfo $file
     *
     * @return array of Scorm resources
     */
    private function generateScosFromScormArchive(\SplFileInfo $file)
    {
        $contents = '';
        $zip = new \ZipArchive();

        $zip->open($file);
        $stream = $zip->getStream("imsmanifest.xml");

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

        $scos = $this->parseOrganizationsNode($dom);

        return $scos;
    }

    /**
     * Looks for the organization to use
     *
     * @param \DOMDocument $dom
     * @return array of Scorm12Sco
     * @throws InvalidScormArchiveException If a default organization
     *         is defined and not found
     */
    private function parseOrganizationsNode(\DOMDocument $dom)
    {
        $organizationsList = $dom->getElementsByTagName('organizations');
        $resources = $dom->getElementsByTagName('resource');

        if ($organizationsList->length > 0) {
            $organizations = $organizationsList->item(0);
            $organization = $organizations->firstChild;

            if (!is_null($organizations->attributes)
                && !is_null($organizations->attributes->getNamedItem('default'))) {

                $defaultOrganization = $organizations->attributes->getNamedItem('default')->nodeValue;
            } else {
                $defaultOrganization = null;
            }
            // No default organization is defined
            if (is_null($defaultOrganization)) {

                while (!is_null($organization)
                    && $organization->nodeName !== 'organization') {

                    $organization = $organization->nextSibling;
                }

                if (is_null($organization)) {

                    return $this->parseResourceNodes($resources);
                }
            }
            // A default organization is defined
            // Look for it
            else {

                while (!is_null($organization)
                    && ($organization->nodeName !== 'organization'
                        || is_null($organization->attributes->getNamedItem('identifier'))
                        || $organization->attributes->getNamedItem('identifier')->nodeValue !== $defaultOrganization)) {

                    $organization = $organization->nextSibling;
                }

                if (is_null($organization)) {

                    throw new InvalidScormArchiveException('default_organization_not_found_message');
                }
            }

            return $this->parseItemNodes($organization, $resources);
        }
    }

    /**
     * Creates defined structure of SCOs
     *
     * @param \DOMNode $source
     * @param \DOMNodeList $resources
     * @return array of Scorm12Sco
     * @throws InvalidScormArchiveException
     */
    private function parseItemNodes(
        \DOMNode $source,
        \DOMNodeList $resources,
        Scorm12Sco $parentSco = null
    )
    {
        $item = $source->firstChild;
        $scos = array();

        while (!is_null($item)) {

            if ($item->nodeName === 'item') {
                $sco = new Scorm12Sco();
                $scos[] = $sco;
                $sco->setScoParent($parentSco);
                $this->findAttrParams($sco, $item, $resources);
                $this->findNodeParams($sco, $item->firstChild);

                if ($sco->getIsBlock()) {
                    $scos[] = $this->parseItemNodes($item, $resources, $sco);
                }
            }
            $item = $item->nextSibling;
        }

        return $scos;
    }

    /**
     * Initializes parameters of the SCO defined in attributes of the node.
     * It also look for the associated resource if it is a SCO and not a block.
     *
     * @param Scorm12Sco $sco
     * @param \DOMNode $item
     * @param \DOMNodeList $resources
     * @throws InvalidScormArchiveException
     */
    private function findAttrParams(
        Scorm12Sco $sco,
        \DOMNode $item,
        \DOMNodeList $resources
    )
    {
        $identifier = $item->attributes->getNamedItem('identifier');
        $isVisible = $item->attributes->getNamedItem('isvisible');
        $identifierRef = $item->attributes->getNamedItem('identifierref');
        $parameters = $item->attributes->getNamedItem('parameters');

        // throws an Exception if identifier is undefined
        if (is_null($identifier)) {
            throw new InvalidScormArchiveException('sco_with_no_identifier_message');
        }
        $sco->setIdentifier($identifier->nodeValue);

        // visible is true by default
        if (!is_null($isVisible) && $isVisible === 'false') {
            $sco->setVisible(false);
        } else {
            $sco->setVisible(true);
        }

        // set parameters for SCO entry resource
        if (!is_null($parameters)) {
            $sco->setParameters($parameters->nodeValue);
        }

        // check if item is a block or a SCO. A block doesn't define identifierref
        if (is_null($identifierRef)) {
            $sco->setIsBlock(true);
        } else {
            $sco->setIsBlock(false);
            // retrieve entry URL
            $sco->setEntryUrl(
                $this->findEntryUrl($identifierRef->nodeValue, $resources)
            );
        }
    }

    /**
     * Searches for the resource with the given id and retrieve URL to its content.
     *
     * @param string $identifierref id of the resource associated to the SCO
     * @param \DOMNodeList $resources
     * @return string URL to the resource associated to the SCO
     * @throws InvalidScormArchiveException
     */
    public function findEntryUrl($identifierref, \DOMNodeList $resources)
    {
        foreach ($resources as $resource) {
            $identifier = $resource->attributes->getNamedItem('identifier');

            if (!is_null($identifier)) {
                $identifierValue = $identifier->nodeValue;

                if ($identifierValue === $identifierref) {
                    $href = $resource->attributes->getNamedItem('href');

                    if (is_null($href)) {

                        throw new InvalidScormArchiveException('sco_resource_without_href_message');
                    }

                    return $href->nodeValue;
                }
            }
        }
        throw new InvalidScormArchiveException('sco_without_resource_message');
    }


    /**
     * Initializes parameters of the SCO defined in children nodes
     *
     * @param Scorm12Sco $sco
     * @param \DOMNode $item
     */
    private function findNodeParams(Scorm12Sco $sco, \DOMNode $item)
    {
        while (!is_null($item)) {

            switch ($item->nodeName) {
                case 'title':
                    $sco->setTitle($item->nodeValue);
                    break;
                case 'adlcp:masteryscore':
                    $sco->setMasteryScore($item->nodeValue);
                    break;
                case 'adlcp:maxtimeallowed':
                    $sco->setMaxTimeAllowed($item->nodeValue);
                    break;
                case 'adlcp:timelimitaction':
                    $action = strtolower($item->nodeValue);

                    if ($action === 'exit,message'
                        || $action === 'exit,no message'
                        || $action === 'continue,message'
                        || $action === 'continue,no message') {

                        $sco->setTimeLimitAction($action);
                    }
                    break;
                case 'adlcp:datafromlms':
                    $sco->setLaunchData($item->nodeValue);
                    break;
                case 'adlcp:prerequisites':
                    $sco->setPrerequisites($item->nodeValue);
                    break;
            }
            $item = $item->nextSibling;
        }
    }

    /**
     * Associates SCORM resource to SCOs and persists them.
     * As array $scos can also contain an array of scos
     * this method is call recursively when an element is an array.
     *
     * @param Scorm12Resource $scormResource
     * @param array $scos Array of Scorm12Sco
     */
    private function persistScos(
        Scorm12Resource $scormResource,
        array $scos
    )
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
     * Unzip a given ZIP file into the web resources directory
     *
     * @param UploadedFile $file
     * @param $hashName name of the destination directory
     */
    private function unzipScormArchive(\SplFileInfo $file, $hashName, $prefix)
    {
        $zip = new \ZipArchive();
        $zip->open($file);
        $destinationDir = $this->scormResourcesPath . $prefix . DIRECTORY_SEPARATOR . $hashName;

        if (!file_exists($destinationDir)) {
            mkdir($destinationDir, 0777, true);
        }
        $zip->extractTo($destinationDir);
        $zip->close();
    }
}
