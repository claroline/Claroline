<?php

namespace Claroline\AudioPlayerBundle\Serializer\Quiz;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AudioPlayerBundle\Entity\Quiz\ItemType\WaveformQuestion;
use Claroline\AudioPlayerBundle\Entity\Quiz\Misc\Section;
use UJM\ExoBundle\Library\Options\Transfer;

class WaveformQuestionSerializer
{
    use SerializerTrait;

    /**
     * Converts a Waveform question into a JSON-encodable structure.
     *
     * @param WaveformQuestion $waveformQuestion
     * @param array            $options
     *
     * @return array
     */
    public function serialize(WaveformQuestion $waveformQuestion, array $options = [])
    {
        $serialized = [
            'file' => $waveformQuestion->getUrl(),
            'tolerance' => $waveformQuestion->getTolerance(),
            'penalty' => $waveformQuestion->getPenalty(),
            'answersLimit' => $waveformQuestion->getAnswersLimit(),
        ];

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $serialized['solutions'] = $this->serializeSolutions($waveformQuestion);
        }

        return $serialized;
    }

    /**
     * Converts raw data into a Waveform question entity.
     *
     * @param array            $data
     * @param WaveformQuestion $waveformQuestion
     * @param array            $options
     *
     * @return WaveformQuestion
     */
    public function deserialize($data, WaveformQuestion $waveformQuestion = null, array $options = [])
    {
        if (empty($waveformQuestion)) {
            $waveformQuestion = new WaveformQuestion();
        }
        $this->sipe('file', 'setUrl', $data, $waveformQuestion);
        $this->sipe('tolerance', 'setTolerance', $data, $waveformQuestion);
        $this->sipe('penalty', 'setPenalty', $data, $waveformQuestion);
        $this->sipe('answersLimit', 'setAnswersLimit', $data, $waveformQuestion);
        $this->deserializeSections($waveformQuestion, $data['solutions']);

        return $waveformQuestion;
    }

    /**
     * Serializes Question solutions.
     *
     * @param WaveformQuestion $waveformQuestion
     *
     * @return array
     */
    private function serializeSolutions(WaveformQuestion $waveformQuestion)
    {
        return array_values(array_map(function (Section $section) {
            $solutionData = [
                'section' => $this->serializeSection($section),
                'score' => $section->getScore(),
            ];

            if ($section->getFeedback()) {
                $solutionData['feedback'] = $section->getFeedback();
            }

            return $solutionData;
        }, $waveformQuestion->getSections()->toArray()));
    }

    /**
     * Deserializes Question sections.
     *
     * @param WaveformQuestion $waveformQuestion
     * @param array            $solutions
     */
    private function deserializeSections(WaveformQuestion $waveformQuestion, array $solutions)
    {
        $sectionsEntities = $waveformQuestion->getSections()->toArray();

        foreach ($solutions as $solutionData) {
            $section = null;

            // Searches for an existing section entity.
            foreach ($sectionsEntities as $entityIndex => $entitySection) {
                /** @var Section $entitySection */
                if ($entitySection->getUuid() === $solutionData['section']['id']) {
                    $section = $entitySection;
                    unset($sectionsEntities[$entityIndex]);
                    break;
                }
            }

            if (!$section) {
                $section = new Section();
                $section->setWaveform($waveformQuestion);
            } else {
                $section->setUuid($solutionData['section']['id']);
            }
            $section->setScore($solutionData['score']);

            if (!empty($solutionData['feedback'])) {
                $section->setFeedback($solutionData['feedback']);
            }

            // Deserializes section definition
            $this->deserializeSection($section, $solutionData['section']);

            $waveformQuestion->addSection($section);
        }

        // Remaining sections are no longer in the question
        foreach ($sectionsEntities as $sectionToRemove) {
            $waveformQuestion->removeSection($sectionToRemove);
        }
    }

    /**
     * Serializes a Section.
     *
     * @param Section $section
     *
     * @return array
     */
    private function serializeSection(Section $section)
    {
        $data = [
            'id' => $section->getUuid(),
            'start' => $section->getStart(),
            'end' => $section->getEnd(),
            'startTolerance' => $section->getStartTolerance(),
            'endTolerance' => $section->getEndTolerance(),
        ];

        if ($section->getColor()) {
            $data['color'] = $section->getColor();
        }

        return $data;
    }

    /**
     * Deserializes a Section.
     *
     * @param Section $section
     * @param array   $data
     */
    private function deserializeSection(Section $section, array $data)
    {
        $this->sipe('start', 'setStart', $data, $section);
        $this->sipe('end', 'setEnd', $data, $section);
        $this->sipe('startTolerance', 'setStartTolerance', $data, $section);
        $this->sipe('endTolerance', 'setEndTolerance', $data, $section);
        $this->sipe('color', 'setColor', $data, $section);
    }
}
