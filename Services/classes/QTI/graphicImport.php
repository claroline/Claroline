<?php

/**
 * To import a QCM question in QTI
 *
 */

namespace UJM\ExoBundle\Services\classes\QTI;

use UJM\ExoBundle\Entity\Coords;
use UJM\ExoBundle\Entity\Document;
use UJM\ExoBundle\Entity\InteractionGraphic;

class graphicImport extends qtiImport {

    protected $interactionGraph;

    /**
     * Implements the abstract method
     *
     * @access public
     * @param qtiRepository $qtiRepos
     * @param DOMElement $assessmentItem assessmentItem of the question to imported
     *
     * @return UJM\ExoBundle\Entity\InteractionGraphic
     */
    public function import(qtiRepository $qtiRepos, $assessmentItem) {
        $this->qtiRepos = $qtiRepos;
        $this->getQTICategory();
        $this->initAssessmentItem($assessmentItem);

        if ($this->qtiIsValid() === false) {

            return false;
        }

        $this->createQuestion();

        $this->createInteraction();
        $this->interaction->setType('InteractionGraphic');
        $this->om->persist($this->interaction);
        $this->om->flush();

        $this->createInteractionGraphic();

        return $this->interactionGraph;
    }

    /**
     * Create the InteractionGraphic object
     *
     * @access protected
     *
     */
    protected function createInteractionGraphic() {
        $spi = $this->assessmentItem->getElementsByTagName("selectPointInteraction")->item(0);
        $ob = $spi->getElementsByTagName('object')->item(0);

        $this->interactionGraph = new InteractionGraphic();
        $this->interactionGraph->setInteraction($this->interaction);
        $this->interactionGraph->setHeight($ob->getAttribute('height'));
        $this->interactionGraph->setWidth($ob->getAttribute('width'));

        $this->om->persist($this->interactionGraph);
        $this->om->flush();

        $this->createCoords();
        $this->createPicture($ob);
    }

    /**
     * Create the Coords
     *
     * @access protected
     *
     */
    protected function createCoords() {
        $am = $this->assessmentItem->getElementsByTagName("areaMapping")->item(0);

            foreach ($am->getElementsByTagName("areaMapEntry") as $areaMapEntry) {
                $tabCoords = explode(',', $areaMapEntry->getAttribute('coords'));
                $coords = new Coords();
                $x = $tabCoords[0] - $tabCoords[2];
                $y = $tabCoords[1] - $tabCoords[2];
                $coords->setValue($x.','.$y);
                $coords->setSize($tabCoords[2] * 2);
                $coords->setShape($areaMapEntry->getAttribute('shape'));
                $coords->setScoreCoords($areaMapEntry->getAttribute('mappedValue'));
                $coords->setColor('white');
                $coords->setInteractionGraphic($this->interactionGraph);
                $this->om->persist($coords);
            }
            $this->om->flush();
    }

    /**
     * Create the Document
     *
     * @param DOMELEMENT $ob object tag
     * @access protected
     *
     */
    protected function createPicture($objectTag) {
        $user    = $this->container->get('security.token_storage')->getToken()->getUser();
        $userDir = './uploads/ujmexo/users_documents/'.$user->getUsername();
        $picName = $this->cpPicture($objectTag->getAttribute('data'), $userDir);

        $document = new Document();
        $document->setLabel($objectTag->nodeValue);
        $document->setType($objectTag->getAttribute('type'));
        $document->setUrl($userDir.'/images/'.$picName);
        $document->setUser($user);

        $this->om->persist($document);
        $this->om->flush();

        $this->interactionGraph->setDocument($document);
        $this->om->persist($this->interactionGraph);
        $this->om->flush();

    }

    /**
     * Copy the picture in the user's directory
     *
     * @param String $picture picture's name
     * @param String $userDir user's directory
     * @access protected
     *
     */
    protected function cpPicture($picture, $userDir) {
        $src = $this->qtiRepos->getUserDir().'/'.$picture;
        $uploadDirectory = $this->container->getParameter('claroline.param.uploads_directory');

        if (!is_dir($uploadDirectory . '/ujmexo/')) {
            mkdir($uploadDirectory . '/ujmexo/');
        }
        if (!is_dir($uploadDirectory . '/ujmexo/users_documents/')) {
            mkdir($uploadDirectory . '/ujmexo/users_documents/');
        }

        if (!is_dir($userDir)) {
            $dirs = array('audio','images','media','video');
            mkdir($userDir);

            foreach ($dirs as $dir) {
                mkdir($userDir.'/'.$dir);
            }
        }

        $picName = $this->getPictureName($picture);
        $dest = $userDir.'/images/'.$picName;
        $i = 1;
        while (file_exists($dest)) {
            $picName = $i.'_'.$this->getPictureName($picture);
            $dest = $userDir.'/images/'.$picName;
            $i++;
        }

        copy($src, $dest);

        return $picName;
    }

    /**
     *
     * @access private
     *
     * @param String $picture
     *
     * @return String
     */
    private function getPictureName($picture)
    {
        $dirs = explode('/', $picture);

        return $dirs[count($dirs) - 1];
    }


    /**
     * Implements the abstract method
     *
     * @access protected
     *
     */
    protected function getPrompt()
    {
        $text = '';
        $ib = $this->assessmentItem->getElementsByTagName("itemBody")->item(0);
        if ($ib->getElementsByTagName("prompt")->item(0)) {
            $prompt = $ib->getElementsByTagName("prompt")->item(0);
            $text = $this->domElementToString($prompt);
            $text = str_replace('<prompt>', '', $text);
            $text = str_replace('</prompt>', '', $text);
            $text = html_entity_decode($text);
        }

        return $text;
    }

    /**
     * abstract method verify the qti
     *
     * @access protected
     *
     * @return boolean
     */
    protected function qtiIsValid()
    {
        $qtiIsValid = true;

        $am = $this->assessmentItem->getElementsByTagName("areaMapping")->item(0);
        if (!$am) {
            $qtiIsValid = false;
        } else {
            foreach ($am->getElementsByTagName("areaMapEntry") as $areaMapEntry) {
                    if ($areaMapEntry->getAttribute('coords') == '') {
                        $qtiIsValid = false;
                    }
            }
        }

        return $qtiIsValid;
    }
}
