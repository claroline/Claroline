<?php

namespace UJM\ExoBundle\Serializer\Misc;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\Misc\Keyword;

/**
 * Serializer for keyword data.
 */
class KeywordSerializer
{
    use SerializerTrait;

    public function getName(): string
    {
        return 'exo_keyword';
    }

    public function getClass(): string
    {
        return Keyword::class;
    }

    public function serialize(Keyword $keyword, array $options = []): array
    {
        $serialized = [
            'text' => $keyword->getText(),
            'caseSensitive' => $keyword->isCaseSensitive(),
            'score' => $keyword->getScore(),
        ];

        if ($keyword->getFeedback()) {
            $serialized['feedback'] = $keyword->getFeedback();
        }

        return $serialized;
    }

    /**
     * Converts raw data into a Keyword entity.
     */
    public function deserialize(array $data, Keyword $keyword = null, array $options = []): Keyword
    {
        if (empty($keyword)) {
            $keyword = new Keyword();
        }
        $cleanedText = trim(strip_tags(html_entity_decode($data['text'])));
        $keyword->setText($cleanedText);

        $this->sipe('caseSensitive', 'setCaseSensitive', $data, $keyword);
        $this->sipe('score', 'setScore', $data, $keyword);
        $this->sipe('feedback', 'setFeedback', $data, $keyword);

        return $keyword;
    }

    /**
     * Updates a collection of keywords entities from raw data.
     * The one which are not in `$keywordCollection` are removed from the entity collection.
     *
     * @param Keyword[] $keywordEntities
     *
     * @return Keyword[] - the list of updated Keyword entities (and without the one no longer in `$keywordCollection`)
     */
    public function deserializeCollection(array $keywordCollection, array $keywordEntities, array $options = []): array
    {
        $keywords = [];

        foreach ($keywordCollection as $keywordData) {
            $keyword = null;

            // Searches for an existing keyword entity.
            foreach ($keywordEntities as $entityKeyword) {
                if ($entityKeyword->getText() === $keywordData['text']
                    && $entityKeyword->isCaseSensitive() === $keywordData['caseSensitive']) {
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
