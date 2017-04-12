<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Content\Image;
use UJM\ExoBundle\Entity\ItemType\GraphicQuestion;
use UJM\ExoBundle\Entity\Misc\Area;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Serializer\SerializerInterface;

/**
 * @DI\Service("ujm_exo.serializer.question_graphic")
 */
class GraphicQuestionSerializer implements SerializerInterface
{
    /**
     * @var FileUtilities
     */
    private $fileUtils;

    /**
     * GraphicQuestionSerializer constructor.
     *
     * @DI\InjectParams({
     *     "fileUtils" = @DI\Inject("claroline.utilities.file")
     * })
     *
     * @param FileUtilities $fileUtils
     */
    public function __construct(FileUtilities $fileUtils)
    {
        $this->fileUtils = $fileUtils;
    }

    /**
     * Converts a Graphic question into a JSON-encodable structure.
     *
     * @param GraphicQuestion $graphicQuestion
     * @param array           $options
     *
     * @return \stdClass
     */
    public function serialize($graphicQuestion, array $options = [])
    {
        $questionData = new \stdClass();

        $questionData->image = $this->serializeImage($graphicQuestion);
        $questionData->pointers = $graphicQuestion->getAreas()->count();

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $questionData->solutions = $this->serializeSolutions($graphicQuestion);
        }

        return $questionData;
    }

    /**
     * Converts raw data into a Graphic question entity.
     *
     * @param \stdClass       $data
     * @param GraphicQuestion $graphicQuestion
     * @param array           $options
     *
     * @return GraphicQuestion
     */
    public function deserialize($data, $graphicQuestion = null, array $options = [])
    {
        if (empty($graphicQuestion)) {
            $graphicQuestion = new GraphicQuestion();
        }

        $this->deserializeImage($graphicQuestion, $data->image);
        $this->deserializeAreas($graphicQuestion, $data->solutions);

        return $graphicQuestion;
    }

    /**
     * Serializes the Question image.
     *
     * @param GraphicQuestion $graphicQuestion
     *
     * @return \stdClass
     */
    private function serializeImage(GraphicQuestion $graphicQuestion)
    {
        $questionImg = $graphicQuestion->getImage();

        $image = new \stdClass();

        if ($questionImg) { // to handle old questions which may have no image
            $image->id = $questionImg->getUuid();
            $image->type = $questionImg->getType();

            if (strpos($questionImg->getUrl(), './') === 0) {
                // the way URLs were written previously isn't spec compliant
                $image->url = substr($questionImg->getUrl(), 2);
            } else {
                $image->url = $questionImg->getUrl();
            }

            $image->width = $questionImg->getWidth();
            $image->height = $questionImg->getHeight();
        }

        return $image;
    }

    /**
     * Deserializes the Question image.
     *
     * @param GraphicQuestion $graphicQuestion
     * @param \stdClass       $imageData
     */
    private function deserializeImage(GraphicQuestion $graphicQuestion, \stdClass $imageData)
    {
        $image = $graphicQuestion->getImage() ?: new Image();

        $image->setUuid($imageData->id);
        $image->setType($imageData->type);
        $image->setTitle($imageData->id);
        $image->setWidth($imageData->width);
        $image->setHeight($imageData->height);

        $objectClass = get_class($graphicQuestion);
        $objectUuid = $graphicQuestion->getQuestion() ? $graphicQuestion->getQuestion()->getUuid() : null;
        $title = $graphicQuestion->getQuestion() ? $graphicQuestion->getQuestion()->getTitle() : null;

        $typeParts = explode('/', $imageData->type);
        if (isset($imageData->data)) {
            $imageName = "{$imageData->id}.{$typeParts[1]}";
            $publicFile = $this->fileUtils->createFileFromData(
                $imageData->data,
                $imageName,
                $objectClass,
                $objectUuid,
                $title,
                $objectClass
            );
            if ($publicFile) {
                $image->setUrl($publicFile->getUrl());
            }
        } elseif (isset($imageData->url)) {
            $image->setUrl($imageData->url);
        }

        $graphicQuestion->setImage($image);
    }

    /**
     * Serializes Question solutions.
     *
     * @param GraphicQuestion $graphicQuestion
     *
     * @return array
     */
    private function serializeSolutions(GraphicQuestion $graphicQuestion)
    {
        return array_map(function (Area $area) {
            $solutionData = new \stdClass();
            $solutionData->area = $this->serializeArea($area);
            $solutionData->score = $area->getScore();
            $solutionData->feedback = $area->getFeedback();

            return $solutionData;
        }, $graphicQuestion->getAreas()->toArray());
    }

    /**
     * Deserializes Question areas.
     *
     * @param GraphicQuestion $graphicQuestion
     * @param array           $solutions
     */
    private function deserializeAreas(GraphicQuestion $graphicQuestion, array $solutions)
    {
        $areaEntities = $graphicQuestion->getAreas()->toArray();

        foreach ($solutions as $solutionData) {
            $area = null;

            // Searches for an existing area entity.
            foreach ($areaEntities as $entityIndex => $entityArea) {
                /** @var Area $entityArea */
                if ($entityArea->getUuid() === $solutionData->area->id) {
                    $area = $entityArea;
                    unset($areaEntities[$entityIndex]);
                    break;
                }
            }

            $area = $area ?: new Area();
            $area->setUuid($solutionData->area->id);

            $area->setScore($solutionData->score);
            $area->setFeedback($solutionData->feedback);

            // Deserializes area definition
            $this->deserializeArea($area, $solutionData->area);

            $graphicQuestion->addArea($area);
        }

        // Remaining areas are no longer in the question
        foreach ($areaEntities as $areaToRemove) {
            $graphicQuestion->removeArea($areaToRemove);
        }
    }

    /**
     * Serializes an Area.
     *
     * @param Area $area
     *
     * @return \stdClass
     */
    private function serializeArea(Area $area)
    {
        $areaData = new \stdClass();

        $areaData->id = $area->getUuid();
        $areaData->color = $area->getColor();

        $position = explode(',', $area->getValue());

        switch ($area->getShape()) {
            case 'circle':
                $areaData->shape = 'circle';
                $areaData->radius = $area->getSize() / 2;

                // We store the top left corner, so we need to calculate the real center
                $center = $this->serializeCoords($position);
                $center->x += $areaData->radius;
                $center->y += $areaData->radius;
                $areaData->center = $center;

                break;
            // For retro-compatibility purpose.
            // It doesn't exist anymore in the schema and is handled as rect
            case 'square':
                $areaData->shape = 'rect';
                $areaData->coords = [
                    // top-left coords
                    $this->serializeCoords($position),
                    // bottom-right coords
                    $this->serializeCoords([$position[0] + $area->getSize(), $position[1] + $area->getSize()]),
                ];
                break;
            case 'rect':
                $areaData->shape = 'rect';
                $areaData->coords = [
                    $this->serializeCoords(array_slice($position, 0, 2)),
                    $this->serializeCoords(array_slice($position, 2, 2)),
                ];
                break;
        }

        return $areaData;
    }

    /**
     * Deserializes an Area.
     *
     * @param Area      $area
     * @param \stdClass $data
     */
    private function deserializeArea(Area $area, \stdClass $data)
    {
        $area->setColor($data->color);
        $area->setShape($data->shape);

        switch ($data->shape) {
            case 'circle':
                // legacy: the top left corner is stored, not the center
                $x = $data->center->x - $data->radius;
                $y = $data->center->y - $data->radius;
                $area->setValue("{$x},{$y}");
                $area->setSize($data->radius * 2);
                break;
            case 'rect':
                $area->setValue(sprintf('%s,%s,%s,%s',
                    $data->coords[0]->x,
                    $data->coords[0]->y,
                    $data->coords[1]->x,
                    $data->coords[1]->y
                ));
                $area->setSize($data->coords[1]->x - $data->coords[0]->x);
                break;
        }
    }

    /**
     * Serializes Coordinates.
     *
     * @param array $coords
     *
     * @return \stdClass
     */
    private function serializeCoords(array $coords)
    {
        $coordsData = new \stdClass();

        $coordsData->x = (int) $coords[0];
        $coordsData->y = (int) $coords[1];

        return $coordsData;
    }
}
