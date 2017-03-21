<?php

namespace UJM\ExoBundle\Serializer\Misc;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Misc\Keyword;
use UJM\ExoBundle\Library\Serializer\SerializerInterface;

/**
 * Serializer for keyword data.
 *
 * @DI\Service("ujm_exo.serializer.keyword")
 */
class KeywordSerializer implements SerializerInterface
{
    /**
     * Converts a Keyword into a JSON-encodable structure.
     *
     * @param Keyword $keyword
     * @param array   $options
     *
     * @return \stdClass
     */
    public function serialize($keyword, array $options = [])
    {
        $keywordData = new \stdClass();
        $keywordData->text = $keyword->getText();
        $keywordData->caseSensitive = $keyword->isCaseSensitive();
        $keywordData->score = $keyword->getScore();

        if ($keyword->getFeedback()) {
            $keywordData->feedback = $keyword->getFeedback();
        }

        return $keywordData;
    }

    /**
     * Converts raw data into a Keyword entity.
     *
     * @param \stdClass $data
     * @param Keyword   $keyword
     * @param array     $options
     *
     * @return Keyword
     */
    public function deserialize($data, $keyword = null, array $options = [])
    {
        if (empty($keyword)) {
            $keyword = new Keyword();
        }

        $cleanedText = trim(strip_tags(html_entity_decode($data->text)));
        $keyword->setText($cleanedText);
        $keyword->setCaseSensitive($data->caseSensitive);
        $keyword->setScore($data->score);

        if (isset($data->feedback)) {
            $keyword->setFeedback($data->feedback);
        }

        return $keyword;
    }

    /**
     * Updates a collection of keywords entities from raw data.
     * The one which are not in `$keywordCollection` are removed from the entity collection.
     *
     * @param \stdClass[] $keywordCollection
     * @param Keyword[]   $keywordEntities
     * @param array       $options
     *
     * @return Keyword[] - the list of updated Keyword entities (and without the one no longer in `$keywordCollection`)
     */
    public function deserializeCollection(array $keywordCollection, array $keywordEntities, array $options = [])
    {
        $keywords = [];

        foreach ($keywordCollection as $keywordData) {
            $keyword = null;

            // Searches for an existing keyword entity.
            foreach ($keywordEntities as $entityKeyword) {
                if ($entityKeyword->getText() === $keywordData->text
                    && $entityKeyword->isCaseSensitive() === $keywordData->caseSensitive) {
                    $keyword = $entityKeyword;
                    break;
                }
            }

            // Update or create keyword
            $keywords[] = $this->deserialize($keywordData, $keyword, $options);
        }

        return $keywords;
    }
}
