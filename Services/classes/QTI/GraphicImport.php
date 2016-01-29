<?php

/**
 * To import a QCM question in QTI.
 */
namespace UJM\ExoBundle\Services\classes\QTI;

use UJM\ExoBundle\Entity\Coords;
use UJM\ExoBundle\Entity\Picture;
use UJM\ExoBundle\Entity\InteractionGraphic;

class GraphicImport extends QtiImport
{
    protected $interactionGraph;

    /**
     * Implements the abstract method.
     *
     * @param qtiRepository $qtiRepos
     * @param DOMElement    $assessmentItem assessmentItem of the question to imported
     *
     * @return UJM\ExoBundle\Entity\InteractionGraphic
     */
    public function import(qtiRepository $qtiRepos, $assessmentItem)
    {
        $this->qtiRepos = $qtiRepos;
        $this->getQTICategory();
        $this->initAssessmentItem($assessmentItem);
        $this->createQuestion(InteractionGraphic::TYPE);
        $this->createInteractionGraphic();

        return $this->interactionGraph;
    }

    /**
     * Create the InteractionGraphic object.
     */
    protected function createInteractionGraphic()
    {
        $spi = $this->assessmentItem->getElementsByTagName('selectPointInteraction')->item(0);
        $ob = $spi->getElementsByTagName('object')->item(0);

        $this->interactionGraph = new InteractionGraphic();
        $this->interactionGraph->setQuestion($this->question);
        $this->interactionGraph->setHeight($ob->getAttribute('height'));
        $this->interactionGraph->setWidth($ob->getAttribute('width'));

        $this->om->persist($this->interactionGraph);
        $this->om->flush();

        $this->createCoords();
        $this->createPicture($ob);
    }

    /**
     * Create the Coords.
     */
    protected function createCoords() {
        $am = $this->assessmentItem->getElementsByTagName('areaMapping')->item(0);

        foreach ($am->getElementsByTagName('areaMapEntry') as $areaMapEntry) {
            $tabCoords = explode(',', $areaMapEntry->getAttribute('coords'));
            $coords = new Coords();
            $feedback = $areaMapEntry->getElementsByTagName("feedbackInline");
            if ($feedback->item(0)) {
                $feedbackVal = $this->domElementToString($feedback->item(0));
                $feedbackVal = html_entity_decode($feedbackVal);
                $coords->setFeedback($feedbackVal);
                $areaMapEntry->removeChild($feedback->item(0));
            }
            $x = $tabCoords[0] - $tabCoords[2];
            $y = $tabCoords[1] - $tabCoords[2];
            $coords->setValue($x . ',' . $y);
            $coords->setSize($tabCoords[2] * 2);
            $coords->setShape($areaMapEntry->getAttribute('shape'));
            $coords->setScoreCoords($areaMapEntry->getAttribute('mappedValue'));
            $color = $areaMapEntry->getAttribute('color');
            if ($color === '') {
                $coords->setColor('black');
            } else {
                $coords->setColor($color);
            }
            $coords->setInteractionGraphic($this->interactionGraph);
            $this->om->persist($coords);
        }
        $this->om->flush();
    }

    /**
     * Create the Document.
     *
     * @param DOMELEMENT $ob object tag
     */
    protected function createPicture($objectTag)
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $userDir = $this->container->getParameter('claroline.param.uploads_directory').'/ujmexo/users_documents/'.$user->getUsername();
        $picName = $this->cpPicture($objectTag->getAttribute('data'), $userDir);

        $picture = new Picture();
        $picture->setLabel($objectTag->nodeValue);
        $picture->setType($objectTag->getAttribute('type'));
        $picture->setUrl('./uploads/ujmexo/users_documents/'.$user->getUsername().'/images/'.$picName);
        $picture->setUser($user);

        $this->om->persist($picture);
        $this->om->flush();

        $this->interactionGraph->setPicture($picture);
        $this->om->persist($this->interactionGraph);
        $this->om->flush();
    }

    /**
     * Copy the picture in the user's directory.
     *
     * @param String $picture picture's name
     * @param String $userDir user's directory
     */
    protected function cpPicture($picture, $userDir)
    {
        $src = $this->qtiRepos->getUserDir().'/'.$picture;

        if (!is_dir('./uploads/ujmexo/')) {
            mkdir('./uploads/ujmexo/');
        }
        if (!is_dir('./uploads/ujmexo/users_documents/')) {
            mkdir('./uploads/ujmexo/users_documents/');
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
            ++$i;
        }

        copy($src, $dest);

        return $picName;
    }

    /**
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
     * Implements the abstract method.
     */
    protected function getPrompt()
    {
        $text = '';
        $ib = $this->assessmentItem->getElementsByTagName('itemBody')->item(0);
        if ($ib->getElementsByTagName('prompt')->item(0)) {
            $prompt = $ib->getElementsByTagName('prompt')->item(0);
            $text = $this->domElementToString($prompt);
            $text = str_replace('<prompt>', '', $text);
            $text = str_replace('</prompt>', '', $text);
            $text = html_entity_decode($text);
        }

        return $text;
    }
}
