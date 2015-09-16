<?php

/**
 * To import a question in QTI.
 */
namespace UJM\ExoBundle\Services\classes\QTI;

use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use UJM\ExoBundle\Entity\Category;
use UJM\ExoBundle\Entity\Interaction;
use UJM\ExoBundle\Entity\Question;
use Claroline\CoreBundle\Persistence\ObjectManager;

abstract class QtiImport
{
    protected $om;
    protected $tokenStorageInterface;
    protected $container;
    protected $user;
    protected $qtiRepos;
    protected $qtiCat;
    protected $question;
    protected $assessmentItem;
    protected $dirQTI;

    /**
     * Constructor.
     *
     *
     * @param \Claroline\CoreBundle\Persistence\ObjectManager                                     $om                    Dependency Injection
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorageInterface Dependency Injection
     * @param \Symfony\Component\DependencyInjection\Container                                    $container
     */
    public function __construct(ObjectManager $om, TokenStorageInterface $tokenStorageInterface, $container)
    {
        $this->om = $om;
        $this->tokenStorageInterface = $tokenStorageInterface;
        $this->container = $container;
        $this->user = $this->tokenStorageInterface->getToken()->getUser();
    }

    /**
     * Create the question objet.
     */
    protected function createQuestion($type)
    {
        $this->objectToResource();
        $this->question = new Question();
        $this->question->setTitle($this->getTitle());
        $this->question->setDateCreate(new \Datetime());
        $this->question->setUser($this->user);
        $this->question->setCategory($this->qtiCat);
        $this->question->setDescription($this->getPrompt());
        $this->question->setType($type);

        if ($feedback = $this->getFeedback()) {
            $this->question->setFeedBack($feedback);
        }

        $this->om->persist($this->question);
        $this->om->flush();
    }

    /**
     * If not exist create for the user the category QTI to import the question.
     */
    private function createQTICategory()
    {
        $this->qtiCat = new Category();
        $this->qtiCat->setValue('QTI');
        $this->qtiCat->setUser($this->user);
        $this->qtiCat->setLocker(false);
        $this->om->persist($this->qtiCat);
        $this->om->flush();
    }

    /**
     * Get the category QTI of the user.
     */
    protected function getQTICategory()
    {
        $this->qtiCat = $this->om
                             ->getRepository('UJMExoBundle:Category')
                             ->findOneBy(array('value' => 'QTI',
                                               'user' => $this->user->getId(), ));
        if ($this->qtiCat == null) {
            $this->createQTICategory();
        }
    }

    /**
     * Get the title of question to import.
     */
    private function getTitle()
    {
        if ($this->assessmentItem->hasAttribute('title')) {
            $title = $this->assessmentItem->getAttribute('title');
        } else {
            $title = $this->container->get('translator')->trans('qti_import_title');
        }

        return $title;
    }

    /**
     * init assessmentItem.
     *
     * @param DOMElement $assessmentItem assessmentItem of the question to imported
     */
    protected function initAssessmentItem($assessmentItem)
    {
        $this->assessmentItem = $assessmentItem;
    }

    /**
     * Get the feedback of question to import.
     */
    private function getFeedback()
    {
        $feedback = null;
        $md = $this->assessmentItem->getElementsByTagName('modalFeedback')->item(0);
        if (isset($md)) {
            $feedback = $md->nodeValue;
        }

        return $feedback;
    }

    /**
     * Get the description of question to import.
     */
    private function getDescription()
    {
        $ib = $this->assessmentItem->getElementsByTagName('itemBody')->item(0);
        $desc = '';
        foreach ($ib->childNodes as $child) {
            if ($child->nodeType === XML_CDATA_SECTION_NODE || $child->nodeType === XML_TEXT_NODE) {
                $desc .= $child->textContent;
            } elseif ($child->nodeName == 'a' || $child->nodeName == 'img') {
                $desc .= $this->domElementToString($child);
                $ib->removeChild($child);
            }
        }
        $this->question->setDescription($desc);
    }

    /**
     * Search object tag to create Claroline resource.
     */
    private function objectToResource()
    {
        $elements = array();
        $objects = $this->assessmentItem->getElementsByTagName('object');
        $ws = $this->user->getPersonalWorkspace();
        $manager = $this->container->get('claroline.manager.resource_manager');
        $filesDirectory = $this->container->getParameter('claroline.param.files_directory');
        $this->getDirQTIImport($ws);
        foreach ($objects as $ob) {
            $fileName = $this->getFileName($ob);
            $tmpFile = $this->qtiRepos->getUserDir().'/'.$fileName;
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $hashName = $this->container->get('claroline.utilities.misc')->generateGuid().'.'.$extension;
            $mimeType = $ob->getAttribute('type');
            $size = filesize($tmpFile);
            $targetFilePath = $filesDirectory.DIRECTORY_SEPARATOR.$hashName;
            copy($tmpFile, $targetFilePath);
            $file = new File();
            $file->setSize($size);
            $file->setName($fileName);
            $file->setHashName($hashName);
            $file->setMimeType($mimeType);
            $abstractResource = $manager->create(
                                    $file,
                                    $manager->getResourceTypeByName('file'),
                                    $this->user,
                                    $ws,
                                    $this->dirQTI
                                );
            if ($ob->parentNode->nodeName != 'selectPointInteraction' &&
                    $ob->parentNode->nodeName != 'hotspotInteraction') {
                $elements[] = array($ob, $abstractResource->getResourceNode());
            }
        }
        $this->callReplaceNode($elements);
    }

    /**
     * @param DomElement <object> $ob
     *
     * @return String
     */
    private function getFileName($ob)
    {
        $fileName = $ob->getAttribute('data');
        $pattern = '(http://|https://)';
        if (preg_match($pattern, $fileName)) {
            $fileURL = $ob->getAttribute('data');
            $fileURLExplode = explode('/', $fileURL);
            $fileName = $fileURLExplode[count($fileURLExplode) - 1];
            $ob->setAttribute('data', $fileName);
            copy($fileURL, $this->qtiRepos->getUserDir().'/'.$fileName);
        }

        return $fileName;
    }

    /**
     * @param array of array $elements
     */
    private function callReplaceNode($elements)
    {
        foreach ($elements as $el) {
            $this->replaceNode($el[0], $el[1]);
        }
    }

    /**
     * Replace the object tag by a link to the Claroline resource.
     *
     *
     * @param DOMNodelist::item                    $ob           element object
     * @param Claroline\CoreBundle\Entity\Resource $resourceNode
     */
    private function replaceNode($ob, $resourceNode)
    {
        $mimeType = $ob->getAttribute('type');
        if (strpos($mimeType, 'image/') !== false) {
            $url = $this->container->get('router')
                        ->generate('claro_file_get_media',
                                array('node' => $resourceNode->getId())
                          );
            $imgTag = $this->assessmentItem->ownerDocument->createElement('img');

            $styleAttr = $this->assessmentItem->ownerDocument->createAttribute('style');
            $srcAttr = $this->assessmentItem->ownerDocument->createAttribute('src');
            $altAttr = $this->assessmentItem->ownerDocument->createAttribute('alt');

            $styleAttr->value = 'max-width: 100%;';
            $srcAttr->value = $url;
            $altAttr->value = $resourceNode->getName();

            $imgTag->appendChild($styleAttr);
            $imgTag->appendChild($srcAttr);
            $imgTag->appendChild($altAttr);

            $ob->parentNode->replaceChild($imgTag, $ob);
        } else {
            $url = $this->container->get('router')
                                   ->generate('claro_resource_open',
                                           array('resourceType' => $resourceNode->getResourceType()->getName(),
                                                 'node' => $resourceNode->getId(),
                                     ));
            $aTag = $this->assessmentItem->ownerDocument->createElement('a', $resourceNode->getName());
            $hrefAttr = $this->assessmentItem->ownerDocument->createAttribute('href');
            $hrefAttr->value = $url;
            $aTag->appendChild($hrefAttr);
            $ob->parentNode->replaceChild($aTag, $ob);
        }
    }

    /**
     * Create a directory in the personal workspace of user to import documents.
     *
     *
     * @param Claroline\CoreBundle\Entity\Workspace
     */
    private function createDirQTIImport($ws)
    {
        $manager = $this->container->get('claroline.manager.resource_manager');
        $parent = $this->om
                       ->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                       ->findWorkspaceRoot($ws);
        $dir = new Directory();
        $dir->setName('QTI_SYS');
        $abstractResource = $manager->create(
                                    $dir,
                                    $manager->getResourceTypeByName('directory'),
                                    $this->user,
                                    $ws,
                                    $parent
                                );
        $this->dirQTI = $abstractResource->getResourceNode();
    }

    /**
     * Get the resource QTI_SYS.
     *
     *
     * @param Claroline\CoreBundle\Entity\Workspace
     */
    private function getDirQTIImport($ws)
    {
        $this->dirQTI = $this->om
                             ->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                             ->findOneBy(array('workspace' => $ws, 'name' => 'QTI_SYS'));

        if (!is_object($this->dirQTI)) {
            $this->createDirQTIImport($ws);
        }
    }

    /**
     * To convet a domElement to string.
     *
     *
     * @param DOMNodelist::item $domEl element of dom
     *
     * @return String
     */
    protected function domElementToString($domEl)
    {
        $text = $this->assessmentItem->ownerDocument->saveXML($domEl);
        $text = trim($text);
        //delete the line break in $text
        $text = str_replace(CHR(10), '', $text);
        $text = str_replace(CHR(13), '', $text);
        //delete CDATA
        $text = str_replace('<![CDATA[', '', $text);
        $text = str_replace(']]>', '', $text);

        return $text;
    }

    /**
     * abstract method to import a question.
     *
     * @param qtiRepository $qtiRepos
     * @param DOMElement    $assessmentItem assessmentItem of the question to imported
     *
     * @return UJM\ExoBundle\Entity\InteractionQCM or InteractionGraphic or ....
     */
    abstract public function import(qtiRepository $qtiRepos, $assessmentItem);

    /**
     * abstract method to get the prompt.
     */
    abstract protected function getPrompt();
}
