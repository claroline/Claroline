<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\ItemType\ClozeQuestion;
use UJM\ExoBundle\Entity\Misc\Hole;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * @DI\Service("ujm_exo.validator.answer_cloze")
 */
class ClozeAnswerValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'answer-data/cloze/schema.json';
    }

     /**
      * Performs additional validations.
      *
      * @param array $answerData
      * @param array $options
      *
      * @return array
      */
     public function validateAfterSchema($answerData, array $options = [])
     {
         /** @var ClozeQuestion $question */
         $question = !empty($options[Validation::QUESTION]) ? $options[Validation::QUESTION] : null;

         if (empty($question)) {
             throw new \LogicException('Answer validation : Cannot perform additional validation without question.');
         }

         $holeIds = array_map(function (Hole $hole) {
             return $hole->getUuid();
         }, $question->getHoles()->toArray());

         foreach ($answerData as $answer) {
             if (empty($answer->holeId)) {
                 return ['Answer `holeId` cannot be empty'];
             }

             if (!in_array($answer->holeId, $holeIds)) {
                 return ['Answer array identifiers must reference question holes'];
             }
         }

         return [];
     }
}
