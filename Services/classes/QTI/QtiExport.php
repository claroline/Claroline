<?php

/**
 * To export a question in QTI.
 */

namespace UJM\ExoBundle\Services\classes\QTI;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use UJM\ExoBundle\Entity\Question;

abstract class QtiExport
{
    protected $doctrine;
    protected $tokenStorageInterface;
    protected $container;
    protected $qtiRepos;
    protected $node;
    protected $document;
    protected $responseDeclaration = array();
    protected $nbResponseDeclaration = 0;
    protected $outcomeDeclaration;
    protected $modalFeedback;
    protected $itemBody;
    protected $question;
    protected $resourcesLinked = array();

    /**
     * Constructor.
     *
     * @access public
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry                                            $doctrine              Dependency Injection
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorageInterface Dependency Injection
     * @param \Symfony\Component\DependencyInjection\Container                                    $container
     */
    public function __construct(Registry $doctrine, TokenStorageInterface $tokenStorageInterface, $container)
    {
        $this->doctrine = $doctrine;
        $this->tokenStorageInterface = $tokenStorageInterface;
        $this->container = $container;
        $this->document = new \DOMDocument();
        $this->document->preserveWhiteSpace = false;
        $this->document->formatOutput = true;
    }

    /**
     * Generate object tags for the attached files.
     *
     * @access protected
     *
     */
    protected function objetcTags()
    {
        $prompt = $this->document->getElementsByTagName('prompt')->item(0);
        $txt = html_entity_decode($prompt->nodeValue);
        $regex = '(<a.*?</a>)';
        preg_match_all($regex, $txt, $matches);
        foreach ($matches[0] as $matche) {
            $url = substr($matche, stripos($matche, 'href="/') + 7, stripos($matche, '">') - 10);
            $url = explode('/', $url);
            $nodeId = $url[6];
            echo $nodeId.'<br>';
        }
    }

    /**
     * Generate head of QTI.
     *
     * @access protected
     *
     * @param String $identifier type question
     * @param String $title      title of question
     */
    protected function qtiHead($identifier, $title)
    {
        $this->node = $this->document->CreateElement('assessmentItem');
        $this->node->setAttribute('xmlns', 'http://www.imsglobal.org/xsd/imsqti_v2p1');
        $this->node->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $this->node->setAttribute('xsi:schemaLocation', 'http://www.imsglobal.org/xsd/imsqti_v2p1 http://www.imsglobal.org/xsd/imsqti_v2p1.xsd');
        $this->node->setAttribute('identifier', $identifier);
        $this->node->setAttribute('title', $title);
        $this->node->setAttribute('adaptive', 'false');
        $this->node->setAttribute('timeDependent', 'false');
        $this->document->appendChild($this->node);
    }

    /**
     * Add a new tag responseDeclaration to node.
     *
     * @access protected
     *
     * @param String $baseType
     */
    protected function qtiResponseDeclaration($identifier, $baseType, $cardinality)
    {
        $this->responseDeclaration[$this->nbResponseDeclaration] = $this->document->CreateElement('responseDeclaration');

        $newRespDec = $this->responseDeclaration[$this->nbResponseDeclaration];
        $newRespDec->setAttribute('identifier', $identifier);
        $newRespDec->setAttribute('cardinality', $cardinality);
        $newRespDec->setAttribute('baseType', $baseType);
        $this->node->appendChild($newRespDec);

        ++$this->nbResponseDeclaration;
    }

    /**
     * add the tag outcomeDeclaration to the node.
     *
     * @access protected
     */
    protected function qtiOutComeDeclaration()
    {
        $this->outcomeDeclaration = $this->document->CreateElement('outcomeDeclaration');
        $this->outcomeDeclaration->setAttribute('identifier', 'SCORE');
        $this->outcomeDeclaration->setAttribute('cardinality', 'single');
        $this->outcomeDeclaration->setAttribute('baseType', 'float');
        $this->node->appendChild($this->outcomeDeclaration);
    }

    /**
     * add the tag modalFeedback to the node.
     *
     *
     * @param String $feedBack
     */
    protected function qtiFeedBack($feedBack)
    {
        $this->modalFeedback = $this->document->CreateElement('modalFeedback');
        $this->modalFeedback->setAttribute("outcomeIdentifier","FEEDBACK");
        $this->modalFeedback->setAttribute("identifier","COMMENT");
        $this->modalFeedback->setAttribute("showHide","show");

        $body = $this->qtiExportObject($feedBack);
        foreach ($body->childNodes as $child) {
            $feedBackNew = $this->document->importNode($child, true);
            $this->modalFeedback->appendChild($feedBackNew);
        }

        $this->node->appendChild($this->modalFeedback);
    }

    /**
     * add a tag p in itemBody for the description.
     */
    protected function qtiDescription()
    {
        $dom = new \DOMDocument();
        $describe = $this->question->getDescription();
        if ($describe != null && $describe != '') {
            $body = $this->qtiExportObject($describe);
            foreach ($body->childNodes as $child) {
                $node = $dom->importNode($child, true);
                $dom->appendChild($node);
            }
            $newDesc = $dom->saveHTML();
            $describeTag = $this->document->createCDATASection($newDesc);
            $this->itemBody->appendChild($describeTag);
        }
    }

    /**
     * Managing the resource export, format QTI
     * @param String $str
     * @return DOMElement
     */
    protected function qtiExportObject($str) {
        $dom = new \DOMDocument();
        $dom->loadHTML($str);
        $this->imgToObject($dom);
        $this->aToObject($dom);
        $body = $dom->getElementsByTagName('body')->item(0);
        return $body;
    }

    /**
     * Export atached file
     * @access private
     *
     * @param String $path path of file to export
     *
     * @return \Claroline\CoreBundle\Entity\Resource\File
     */
    private function getFile($path) {
        $urlExplode = explode('/', $path);
        $idNode = end($urlExplode);
        $objSrc =  $this->doctrine->getManager()->getRepository('ClarolineCoreBundle:Resource\File')->findOneBy(array('resourceNode' => $idNode));
        $src = $this->container->getParameter('claroline.param.files_directory').'/'.$objSrc->getHashName();
        $name = $objSrc->getResourceNode()->getName();
        $dest = $this->qtiRepos->getUserDir().$name;
        copy($src, $dest);
        $ressource = array ('name' => $name, 'url' => $src);
        $this->resourcesLinked[] = $ressource;
        return $objSrc;
    }

    /**
     * add the tag itemBody in node.
     */
    protected function itemBodyTag()
    {
        $this->itemBody = $this->document->CreateElement('itemBody');
        $this->node->appendChild($this->itemBody);
        $this->qtiDescription();
    }

    /**
     * @param array of String $docLinked attached files
     *
     * @return BinaryFileResponse QTI zip
     */
    protected function getResponse()
    {
        $tmpFileName = $this->qtiRepos->getUserDir().'zip/'.$this->question->getId().'_qestion_qti.zip';
        $zip = new \ZipArchive();
        $zip->open($tmpFileName, \ZipArchive::CREATE);
        $zip->addFile($this->qtiRepos->getUserDir().$this->question->getId().'_qestion_qti.xml', $this->question->getId().'_qestion_qti.xml');

        foreach ($this->resourcesLinked as $file) {
            $zip->addFile($file['url'], $file['name']);
        }

        $zip->close();

        $qtiServ = $this->container->get('ujm.exo_qti');
        $response = $qtiServ->createZip($tmpFileName, $this->question->getId());

        return $response;
    }

    /**
     * add the dom in a DomElement
     *
     * @access protected
     *
     * @param DOMElement $domEl
     * @param String $label
     *
     */
    protected function getDomEl($domEl, $label)
    {
       //Managing the resource export
        $body = $this->qtiExportObject($label);
        foreach ($body->childNodes as $child) {
            $labelNew = $this->document->importNode($child, true);
            $domEl->appendChild($labelNew);
        }
    }

    /**
     * Convert img tag to object tag
     * @access protected
     *
     * @param \DOMDocument $DOMdoc
     *
     */
    protected function imgToObject($DOMdoc)
    {
        $tagsImg = $DOMdoc->getElementsByTagName('img');
        foreach ($tagsImg as $img) {
            $object = $DOMdoc->CreateElement('object');
            //Copy the image in the archiv
            $src = $img->getAttribute('src');
            $file = $this->getFile($src);
            $object->setAttribute("data", $file->getResourceNode()->getName());
            $objecttxt = $DOMdoc->CreateTextNode($file->getResourceNode()->getName());
            $object->appendChild($objecttxt);
            $object->setAttribute("type", $file->getResourceNode()->getMimeType());
            //Creating one table to replace the tags
            $elements[] = array($object, $img);
        }
        //Replaces image tag by the object tag
        if (!empty($elements)) {
            foreach ($elements as $el) {
                $el[1]->parentNode->replaceChild($el[0], $el[1]);
            }
        }
    }
    /**
     * Convert a tag to object tag
     * @access protected
     *
     * @param \DOMDocument $DOMdoc
     *
     */
    protected function aToObject($DOMdoc)
    {
        $aTags = $DOMdoc->getElementsByTagName('a');
        foreach ($aTags as $aTag) {
            $object = $DOMdoc->CreateElement('object');
            //Copy the file in the archive
            $path = $aTag->getAttribute('href');
            $file = $this->getFile($path);
            $object->setAttribute("data", $file->getResourceNode()->getName());
            $object->setAttribute("type", $file->getResourceNode()->getMimeType());
            //Creating one table to replace the tags
            $elements[] = array($object, $aTag);
        }
        //Replaces image tag by the object tag
        if (!empty($elements)) {
            foreach ($elements as $el) {
                $el[1]->parentNode->replaceChild($el[0], $el[1]);
            }
        }
    }


    /**
     * abstract method to export the question.
     *
     * @access public
     * @param Question $question
     * @param qtiRepository $qtiRepos
     */
    abstract public function export(Question $question, qtiRepository $qtiRepos);

    /**
     * abstract method
     * Add the tag correctResponse in responseDeclaration.
     */
    abstract protected function correctResponseTag();

    /**
     * abstract method
     * Add the tag prompt.
     */
    abstract protected function promptTag();
}
