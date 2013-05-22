<?php

namespace Claroline\ScormBundle\Listener;

use Claroline\CoreBundle\Library\Event\CopyResourceEvent;
use Claroline\CoreBundle\Library\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Event\CreateResourceEvent;
use Claroline\CoreBundle\Library\Event\OpenResourceEvent;
use Claroline\CoreBundle\Library\Event\DeleteResourceEvent;
//use Claroline\CoreBundle\Library\Event\DownloadResourceEvent;
use Claroline\ScormBundle\Entity\Scorm;
use Claroline\ScormBundle\Entity\ScormInfo;
use Claroline\ScormBundle\Form\ScormType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Service
 */
class ScormListener extends ContainerAware
{
    protected $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @DI\Observe("create_form_claroline_scorm")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new ScormType, new Scorm());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:create_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'claroline_scorm'
            )
        );
        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("create_claroline_scorm")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new ScormType(), new Scorm());
        $form->bindRequest($request);

        if ($form->isValid()) {
            $scorm = $form->getData();
            $tmpFile = $form->get('file')->getData();
            $fileName = $tmpFile->getClientOriginalName();
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $hashName = $this->container->get('claroline.resource.utilities')->generateGuid() . "." . $extension;
            $tmpFile->move($this->container->getParameter('claroline.param.files_directory'), $hashName);

            $path = $this->container->getParameter('claroline.param.files_directory') . DIRECTORY_SEPARATOR . $hashName;
            $zip = new \ZipArchive();

            if ($zip->open($path) === true) {
                $stream = $zip->getStream("imsmanifest.xml");

                if ($stream) {
                    $contents = '';

                    while (!feof($stream)) {
                        $contents .= fread($stream, 2);
                    }
                    $dom = new \DOMDocument();
                    $dom->loadXML($contents);

                    $items = $dom->getElementsByTagName('item');
                    $scoResources = $dom->getElementsByTagName('resource');
                    $resources = array();

                    foreach ($items as $item) {
                        $ref = $item->attributes->getNamedItem('identifierref');

                        if (!is_null($ref)) {
                            $identifierRef = $ref->nodeValue;
                            $title = $item->getElementsByTagName('title')->item(0)->nodeValue;
                            $launchDatas = $item->getElementsByTagNameNS(
                                $item->lookupNamespaceUri('adlcp'),
                                'datafromlms'
                            );
                            $masteryScores = $item->getElementsByTagNameNS(
                                $item->lookupNamespaceUri('adlcp'),
                                'masteryscore'
                            );

                            if ($launchDatas->length > 0) {
                                $launchData = $launchDatas->item(0)->nodeValue;
                            } else {
                                $launchData = '';
                            }

                            if ($masteryScores->length > 0) {
                                $masteryScore = intval($masteryScores->item(0)->nodeValue);
                            } else {
                                $masteryScore = -1;
                            }

                            foreach ($scoResources as $scoResource) {
                                $identifier = $scoResource->attributes->getNamedItem('identifier');
                                $href = $scoResource->attributes->getNamedItem('href');
                                $scormType = $scoResource->attributes->getNamedItemNS(
                                    $scoResource->lookupNamespaceUri('adlcp'),
                                    'scormtype'
                                );

                                // For compatibility with Raptivity scorm 1.2 package
                                if (is_null($scormType)) {
                                    $scormType = $scoResource->attributes->getNamedItemNS(
                                        $scoResource->lookupNamespaceUri('adlcp'),
                                        'scormType'
                                    );
                                }

                                if (!is_null($identifier)
                                    && !is_null($scormType)
                                    && !is_null($href)
                                    && $identifier->nodeValue === $identifierRef
                                    && $scormType->nodeValue === 'sco') {

                                    $scoUrl = $href->nodeValue;

                                    $scorm = new Scorm();
                                    $scorm->setName($title);
                                    $scorm->setEntryUrl($scoUrl);
                                    $scorm->setLaunchData($launchData);
                                    $scorm->setMasteryScore($masteryScore);
                                    $scorm->setHashName($hashName);
                                    $resources[] = $scorm;
                                    break;
                                }
                            }
                        }
                    }

                    if (count($resources) === 1) {
                        $event->setResource($resources[0]);
                    } else {
                        $event->setResources($resources);
                    }
                    $this->unzipTmpFile($hashName);
                } else {
                    throw new \Exception("File imsmanifest.xml must be in the root directory of the archive");
                }
            }
            $event->stopPropagation();

            return;
        }

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:create_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => $event->getResourceType()
            )
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_claroline_scorm")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $ds = DIRECTORY_SEPARATOR;
        $scorm = $event->getResource();
        $hashName = $scorm->getHashName();
        $relativePath = pathinfo($hashName, PATHINFO_FILENAME)
            . $ds
            . $scorm->getEntryUrl();
        $route = $this->container->get('router')->getContext()->getBaseUrl();
        $fp = preg_replace('"/web/app_dev.php$"', "/web/HTMLPage/$relativePath", $route);

        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->container->get('doctrine.orm.entity_manager');
        $scormInfo = $em->getRepository('ClarolineScormBundle:ScormInfo')
            ->findOneBy(array('user' => $user->getId(), 'scorm' => $scorm->getId()));

        if (is_null($scormInfo)) {
            $scormInfo = new ScormInfo();
            $scormInfo->setUser($user);
            $scormInfo->setScorm($scorm);
            $scormInfo->setScoreRaw(-1);
            $scormInfo->setScoreMax(-1);
            $scormInfo->setScoreMin(-1);
            $scormInfo->setLessonStatus("not attempted");
            $scormInfo->setSuspendData("");
            $scormInfo->setEntry("ab-initio");
            $scormInfo->setLessonLocation("");
            $scormInfo->setCredit("no-credit");
            $scormInfo->setTotalTime(0);
            $scormInfo->setSessionTime(0);
            $scormInfo->setLessonMode("normal");
            $scormInfo->setExitMode("");
        }
        $content = $this->container->get('templating')->render(
            'ClarolineScormBundle::scorm.html.twig',
            array(
                'scormUrl' => $fp,
                'workspace' => $scorm->getWorkspace(),
                'scormInfo' => $scormInfo
            )
        );
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_claroline_scorm")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($event->getResource());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_claroline_scorm")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $resource = $event->getResource();
        $copy = new Scorm();
        $copy->setHashName($resource->getHashName());
        $copy->setLaunchData($resource->getLaunchData());
        $copy->setMasteryScore($resource->getMasteryScore());
        $copy->setEntryUrl($resource->getEntryUrl());
        $copy->setName($resource->getName());
        $event->setCopy($copy);
        $event->stopPropagation();
    }

    private function unzipTmpFile($hashName)
    {
        $path = $this->container->getParameter('claroline.param.files_directory') . DIRECTORY_SEPARATOR . $hashName;
        $zip = new \ZipArchive();

        if ($zip->open($path) === true) {
            $zip->extractTo(
                $this->container->getParameter('claroline.site.directory')
                . DIRECTORY_SEPARATOR
                . pathinfo($hashName, PATHINFO_FILENAME)
                . DIRECTORY_SEPARATOR
            );
            $zip->close();
        } else {
            return 0;
        }
    }
}