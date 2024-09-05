<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Manager\FileManager;
use UJM\ExoBundle\Entity\Content\Image;
use UJM\ExoBundle\Entity\ItemType\GraphicQuestion;
use UJM\ExoBundle\Entity\Misc\Area;
use UJM\ExoBundle\Library\Options\Transfer;

class GraphicQuestionSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly FileManager $fileManager
    ) {
    }

    public function getName(): string
    {
        return 'exo_question_graphic';
    }

    /**
     * Converts a Graphic question into a JSON-encodable structure.
     */
    public function serialize(GraphicQuestion $graphicQuestion, array $options = []): array
    {
        $serialized = [
            'image' => $this->serializeImage($graphicQuestion),
            'pointers' => $graphicQuestion->getAreas()->count(),
            'pointerMode' => 'pointer', // the feature is not yet implemented, but the JSON schema already requires it
        ];

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $serialized['solutions'] = $this->serializeSolutions($graphicQuestion);
        }

        return $serialized;
    }

    /**
     * Converts raw data into a Graphic question entity.
     */
    public function deserialize(array $data, GraphicQuestion $graphicQuestion = null, ?array $options = []): GraphicQuestion
    {
        if (empty($graphicQuestion)) {
            $graphicQuestion = new GraphicQuestion();
        }

        $this->deserializeImage($graphicQuestion, $data['image']);
        $this->deserializeAreas($graphicQuestion, $data['solutions']);

        return $graphicQuestion;
    }

    private function serializeImage(GraphicQuestion $graphicQuestion): array
    {
        $questionImg = $graphicQuestion->getImage();

        $image = [];

        if ($questionImg) { // to handle old questions which may have no image
            $image['id'] = $questionImg->getUuid();
            $image['type'] = $questionImg->getType();

            if (0 === strpos($questionImg->getUrl(), './')) {
                // the way URLs were written previously isn't spec compliant
                $image['url'] = substr($questionImg->getUrl(), 2);
            } else {
                $image['url'] = $questionImg->getUrl();
            }

            $image['width'] = $questionImg->getWidth();
            $image['height'] = $questionImg->getHeight();
        }

        return $image;
    }

    private function deserializeImage(GraphicQuestion $graphicQuestion, array $imageData): void
    {
        $image = $graphicQuestion->getImage() ?: new Image();

        $this->sipe('id', 'setUuid', $imageData, $image);
        $this->sipe('type', 'setType', $imageData, $image);
        $this->sipe('id', 'setTitle', $imageData, $image);
        $this->sipe('width', 'setWidth', $imageData, $image);
        $this->sipe('height', 'setHeight', $imageData, $image);

        $objectClass = get_class($graphicQuestion);
        $objectUuid = $graphicQuestion->getQuestion() ? $graphicQuestion->getQuestion()->getUuid() : null;
        $title = $graphicQuestion->getQuestion() ? $graphicQuestion->getQuestion()->getTitle() : null;

        $typeParts = explode('/', $imageData['type']);

        if (isset($imageData['data'])) {
            $imageName = "{$imageData['id']}.{$typeParts[1]}";
            $publicFile = $this->fileManager->createFileFromData(
                $imageData['data'],
                $imageName,
                $objectClass,
                $objectUuid,
                $title
            );

            if ($publicFile) {
                $image->setUrl($publicFile->getUrl());
            }
        } elseif (isset($imageData['url'])) {
            $image->setUrl($imageData['url']);
        }

        $graphicQuestion->setImage($image);
    }

    private function serializeSolutions(GraphicQuestion $graphicQuestion): array
    {
        return array_map(function (Area $area) {
            $solutionData = [
                'area' => $this->serializeArea($area),
                'score' => $area->getScore(),
            ];

            if ($area->getFeedback()) {
                $solutionData['feedback'] = $area->getFeedback();
            }

            return $solutionData;
        }, $graphicQuestion->getAreas()->toArray());
    }

    private function deserializeAreas(GraphicQuestion $graphicQuestion, array $solutions): void
    {
        $areaEntities = $graphicQuestion->getAreas()->toArray();

        foreach ($solutions as $solutionData) {
            $area = null;

            // Searches for an existing area entity.
            foreach ($areaEntities as $entityIndex => $entityArea) {
                /** @var Area $entityArea */
                if ($entityArea->getUuid() === $solutionData['area']['id']) {
                    $area = $entityArea;
                    unset($areaEntities[$entityIndex]);
                    break;
                }
            }

            $area = $area ?: new Area();
            $area->setUuid($solutionData['area']['id']);
            $area->setScore($solutionData['score']);

            if (!empty($solutionData['feedback'])) {
                $area->setFeedback($solutionData['feedback']);
            }

            // Deserializes area definition
            $this->deserializeArea($area, $solutionData['area']);

            $graphicQuestion->addArea($area);
        }

        // Remaining areas are no longer in the question
        foreach ($areaEntities as $areaToRemove) {
            $graphicQuestion->removeArea($areaToRemove);
        }
    }

    private function serializeArea(Area $area): array
    {
        $areaData = [
            'id' => $area->getUuid(),
            'color' => $area->getColor(),
        ];

        $position = explode(',', $area->getValue());

        switch ($area->getShape()) {
            case 'circle':
                $areaData['shape'] = 'circle';
                $areaData['radius'] = $area->getSize() / 2;

                // We store the top left corner, so we need to calculate the real center
                $center = $this->serializeCoords($position);
                $center['x'] += $areaData['radius'];
                $center['y'] += $areaData['radius'];
                $areaData['center'] = $center;

                break;
                // For retro-compatibility purpose.
                // It doesn't exist anymore in the schema and is handled as rect
            case 'square':
                $areaData['shape'] = 'rect';
                $areaData['coords'] = [
                    // top-left coords
                    $this->serializeCoords($position),
                    // bottom-right coords
                    $this->serializeCoords([$position[0] + $area->getSize(), $position[1] + $area->getSize()]),
                ];
                break;
            case 'rect':
                $areaData['shape'] = 'rect';
                $areaData['coords'] = [
                    $this->serializeCoords(array_slice($position, 0, 2)),
                    $this->serializeCoords(array_slice($position, 2, 2)),
                ];
                break;
        }

        return $areaData;
    }

    private function deserializeArea(Area $area, array $data): void
    {
        if (!empty($data['color'])) {
            $area->setColor($data['color']);
        }

        $area->setShape($data['shape']);

        switch ($data['shape']) {
            case 'circle':
                // legacy: the top left corner is stored, not the center
                $x = $data['center']['x'] - $data['radius'];
                $y = $data['center']['y'] - $data['radius'];
                $area->setValue("{$x},{$y}");
                $area->setSize($data['radius'] * 2);
                break;
            case 'rect':
                $area->setValue(sprintf(
                    '%s,%s,%s,%s',
                    $data['coords'][0]['x'],
                    $data['coords'][0]['y'],
                    $data['coords'][1]['x'],
                    $data['coords'][1]['y']
                ));
                $area->setSize($data['coords'][1]['x'] - $data['coords'][0]['x']);
                break;
        }
    }

    private function serializeCoords(array $coords): array
    {
        return [
            'x' => (int) $coords[0],
            'y' => (int) $coords[1],
        ];
    }
}
