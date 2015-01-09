<?php

/**
 * To import a question in QTI
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Security\Core\SecurityContextInterface;
use UJM\ExoBundle\Entity\Category;
use UJM\ExoBundle\Entity\Interaction;
use UJM\ExoBundle\Entity\Question;

abstract class qtiImport
{
    protected $doctrine;
    protected $securityContext;
    protected $container;
    protected $user;
    protected $qtiRepos;
    protected $qtiCat;
    protected $interaction;
    protected $question;
    protected $document;
    protected $assessmentItem;
    protected $dirQTI;

    /**
     * Constructor
     *
     * @access public
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine Dependency Injection
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext Dependency Injection
     *
     */
    public function __construct(Registry $doctrine, SecurityContextInterface $securityContext, $container)
    {
        $this->doctrine        = $doctrine;
        $this->securityContext = $securityContext;
        $this->container       = $container;
        $this->user            = $this->securityContext->getToken()->getUser();
    }

    /**
     * Create the question objet
     *
     * @access protected
     *
     */
    protected function createQuestion()
    {
        $this->objectToResource();
        $this->question = new Question();
        $this->question->setTitle($this->getTitle());
        $this->question->setDateCreate(new \Datetime());
        $this->question->setUser($this->user);
        $this->question->setCategory($this->qtiCat);
        $this->getDescription();
        $this->doctrine->getManager()->persist($this->question);
        $this->doctrine->getManager()->flush();
    }

    /**
     * Create the interaction objet
     *
     * @access protected
     *
     */
    protected function createInteraction()
    {
        $feedback = $this->getFeedback();
        $this->interaction = new Interaction;
        $this->interaction->setInvite($this->getPrompt());
        if ($feedback != null) {
            $this->interaction->setFeedBack($feedback);
        }
        $this->interaction->setQuestion($this->question);
    }

    /**
     * If not exist create for the user the category QTI to import the question
     *
     * @access private
     *
     */
    private function createQTICategory()
    {
        $this->qtiCat = new Category();
        $this->qtiCat->setValue("QTI");
        $this->qtiCat->setUser($this->user);
        $this->doctrine->getManager()->persist($this->qtiCat);
        $this->doctrine->getManager()->flush();
    }

    /**
     * Get the category QTI of the user
     *
     * @access protected
     *
     */
    protected function getQTICategory()
    {
        $this->qtiCat = $this->doctrine
                             ->getManager()
                             ->getRepository('UJMExoBundle:Category')
                             ->findOneBy(array('value' => 'QTI',
                                               'user' => $this->user->getId()));
        if ($this->qtiCat == null) {
            $this->createQTICategory();
        }
    }

    /**
     * Get the title of question to import
     *
     * @access private
     *
     */
    private function getTitle()
    {
        if ($this->assessmentItem->hasAttribute("title")) {
           $title = $this->assessmentItem->getAttribute("title");
        } else {
            $title = $this->container->get('translator')->trans('qti_import_title');
        }

        return $title;
    }

    /**
     * init assessmentItem
     *
     * @access protected
     *
     */
    protected function initAssessmentItem()
    {
        $root = $this->document->getElementsByTagName('assessmentItem');
        $this->assessmentItem = $root->item(0);
    }

    /**
     * Get the feedback of question to import
     *
     * @access private
     *
     */
    private function getFeedback()
    {
        $feedback = null;
        $md = $this->assessmentItem->getElementsByTagName("modalFeedback")->item(0);
        if (isset($md)) {
           $feedback = $md->nodeValue;
        }

        return $feedback;
    }

    /**
     * Get the description of question to import
     *
     * @access private
     *
     */
    private function getDescription()
    {
        $ib = $this->assessmentItem->getElementsByTagName("itemBody")->item(0);
        $desc = '';
        foreach($ib->childNodes as $child) {
            if ($child->nodeType === XML_CDATA_SECTION_NODE || $child->nodeType === XML_TEXT_NODE) {
                $desc .= $child->textContent;
            } else if ($child->nodeName == 'a' || $child->nodeName == 'img' || $child->nodeName == 'p') {
                if ($child->nodeValue != '' ) {
                    $desc .= $child->nodeValue;
                } else {
                    $desc .= $this->domElementToString($child);
                }
                $ib->removeChild($child);
            }
        }
        $this->question->setDescription($desc);
    }

    /**
     * Search object tag to create Claroline resource
     *
     * @access private
     *
     */
    private function objectToResource()
    {
        $elements = array();
        $objects = $this->assessmentItem->getElementsByTagName('object');
        $ws      = $this->user->getPersonalWorkspace();
        $manager = $this->container->get('claroline.manager.resource_manager');
        $filesDirectory = $this->container->getParameter('claroline.param.files_directory');
        $this->getDirQTIImport($ws);
        foreach ($objects as $ob) {
            $fileName = $ob->getAttribute('data');
            $tmpFile = $this->qtiRepos->getUserDir().'/'.$fileName;
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $hashName = $this->container->get('claroline.utilities.misc')->generateGuid() . '.' . $extension;
            $mimeType = $ob->getAttribute('type');
            $size = filesize($tmpFile);
            $targetFilePath = $filesDirectory . DIRECTORY_SEPARATOR . $hashName;
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
     *
     * @access private
     *
     * @param array of array $elements
     *
     */
    private function callReplaceNode($elements)
    {
        foreach ($elements as $el) {
            $this->replaceNode($el[0], $el[1]);
        }
    }

    /**
     * Replace the object tag by a link to the Claroline resource
     *
     * @access private
     *
     * @param DOMNodelist::item $ob element object
     * @param Claroline\CoreBundle\Entity\Resource $resourceNode
     *
     */
    private function replaceNode($ob, $resourceNode)
    {
        $mimeType = $ob->getAttribute('type');
        if (strpos($mimeType, 'image/') !== false) {
            $url = $this->container->get('router')
                        ->generate('claro_file_get_media',
                                array('node' => $resourceNode->getId())
                          );
            $imgTag    = $this->document->createElement('img');

            $styleAttr = $this->document->createAttribute('style');
            $srcAttr   = $this->document->createAttribute('src');
            $altAttr   = $this->document->createAttribute('alt');

            $styleAttr->value = 'max-width: 100%;';
            $srcAttr->value   = $url;
            $altAttr->value   = $resourceNode->getName();

            $imgTag->appendChild($styleAttr);
            $imgTag->appendChild($srcAttr);
            $imgTag->appendChild($altAttr);

            $ob->parentNode->replaceChild($imgTag, $ob);
        } else {
            $url = $this->container->get('router')
                                   ->generate('claro_resource_open',
                                           array('resourceType' => $resourceNode->getResourceType()->getName() ,
                                                 'node' => $resourceNode->getId()
                                     ));
            $aTag     = $this->document->createElement('a', $resourceNode->getName());
            $hrefAttr = $this->document->createAttribute('href');
            $hrefAttr->value = $url;
            $aTag->appendChild($hrefAttr);
            $ob->parentNode->replaceChild($aTag, $ob);
        }
    }

    /**
     * Create a directory in the personal workspace of user to import documents
     *
     * @access private
     *
     * @param Claroline\CoreBundle\Entity\Workspace
     *
     */
    private function createDirQTIImport($ws)
    {
        $manager = $this->container->get('claroline.manager.resource_manager');
        $parent = $this->doctrine
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
     * Get the resource QTI_SYS
     *
     * @access private
     *
     * @param Claroline\CoreBundle\Entity\Workspace
     *
     */
    private function getDirQTIImport($ws)
    {
        $this->dirQTI = $this->doctrine->getManager()
                             ->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                             ->findOneBy(array('workspace' => $ws, 'name' => 'QTI_SYS'));

        if (!is_object($this->dirQTI)) {
            $this->createDirQTIImport($ws);
        }
    }

    /**
     * To convet a domElement to string
     *
     * @access private
     *
     * @param DOMNodelist::item $domEl element of dom
     *
     * @return String
     *
     */
    protected function domElementToString($domEl)
    {
        $text = $this->document->saveXML($domEl);
        $text = trim($text);
        //delete the line break in $text
        $text = str_replace(CHR(10),"",$text);
        $text = str_replace(CHR(13),"",$text);
        //delete CDATA
        $text = str_replace('<![CDATA[', '', $text);
        $text = str_replace(']]>', '', $text);

        return $text;
    }

    /**
     * abstract method to import a question
     *
     * @access public
     * @param qtiRepository $qtiRepos
     */
    abstract public function import(qtiRepository $qtiRepos, \DOMDocument $document);

    /**
     * abstract method to get the prompt
     *
     * @access protected
     */
    abstract protected function getPrompt();

}
