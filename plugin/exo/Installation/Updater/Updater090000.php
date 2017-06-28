<?php

namespace UJM\ExoBundle\Installation\Updater;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\DBAL\Connection;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Library\Options\ExerciseType;
use UJM\ExoBundle\Library\Options\Recurrence;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Serializer\ExerciseSerializer;
use UJM\ExoBundle\Serializer\Item\ItemSerializer;
use UJM\ExoBundle\Serializer\StepSerializer;

class Updater090000
{
    use LoggableTrait;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ExerciseSerializer
     */
    private $exerciseSerializer;

    /**
     * @var StepSerializer
     */
    private $stepSerializer;

    /**
     * @var ItemSerializer
     */
    private $itemSerializer;

    /**
     * Updater080000 constructor.
     *
     * @param Connection         $connection
     * @param ObjectManager      $om
     * @param ExerciseSerializer $exerciseSerializer
     * @param StepSerializer     $stepSerializer
     * @param ItemSerializer     $itemSerializer
     */
    public function __construct(
        Connection $connection,
        ObjectManager $om,
        ExerciseSerializer $exerciseSerializer,
        StepSerializer $stepSerializer,
        ItemSerializer $itemSerializer)
    {
        $this->connection = $connection;
        $this->om = $om;
        $this->exerciseSerializer = $exerciseSerializer;
        $this->stepSerializer = $stepSerializer;
        $this->itemSerializer = $itemSerializer;
    }

    public function postUpdate()
    {
        $this->updateExerciseTypes();
        $this->updateAnswerData();
        $this->cleanOldPairQuestions();
        $this->updateClozeQuestions();
        $this->updatePapers();
    }

    private function updateExerciseTypes()
    {
        $this->log('Update Exercise types...');

        $types = [
            '1' => ExerciseType::SUMMATIVE,
            '2' => ExerciseType::EVALUATIVE,
            '3' => ExerciseType::FORMATIVE,
        ];

        $sth = $this->connection->prepare('UPDATE ujm_exercise SET `type` = :newType WHERE `type` = :oldType');
        foreach ($types as $oldType => $newType) {
            $sth->execute([
                ':oldType' => $oldType,
                ':newType' => $newType,
            ]);
        }

        $this->log('Enable feedback for formative Exercises...');
        $this->connection
            ->prepare('UPDATE ujm_exercise SET show_feedback = true WHERE `type` = "formative"')
            ->execute();

        $this->log('done !');
    }

    /**
     * The answer data system uses custom encoding rules to converts answer data into string (to be stored in DB).
     *
     * The current methods updates existing data to just use the result of json_encode
     * on API data to in DB. This avoid to add custom logic for all question types.
     *
     * Example for choice answer storage:
     *  - old format : "1;2;3;4"
     *  - new format : "[1,2,3,4]"
     */
    private function updateAnswerData()
    {
        $this->log('Update answers data...');

        // Answer parts
        $choiceSth = $this->connection->prepare('SELECT id, uuid FROM ujm_choice');
        $choiceSth->execute();
        $choices = $choiceSth->fetchAll();

        $labelSth = $this->connection->prepare('SELECT id, uuid FROM ujm_label');
        $labelSth->execute();
        $labels = $labelSth->fetchAll();

        $proposalSth = $this->connection->prepare('SELECT id, uuid FROM ujm_proposal');
        $proposalSth->execute();
        $proposals = $proposalSth->fetchAll();

        $holeSth = $this->connection->prepare('
            SELECT h.id, h.uuid, h.`position`, h.selector, q.uuid AS question_id
            FROM ujm_hole AS h
            JOIN ujm_interaction_hole AS i ON (h.interaction_hole_id = i.id)
            JOIN ujm_question AS q ON (i.question_id = q.id)
        ');
        $holeSth->execute();
        $holes = $holeSth->fetchAll();

        $keywordSth = $this->connection->prepare('SELECT id, `response` FROM ujm_word_response');
        $keywordSth->execute();
        $keywords = $keywordSth->fetchAll();

        // Load answers
        $sth = $this->connection->prepare('
            SELECT q.mime_type, a.id AS answerId, a.response AS data, a.question_id
            FROM ujm_response AS a
            LEFT JOIN ujm_question AS q ON (a.question_id = q.uuid)
            WHERE a.response IS NOT NULL 
              AND a.response != ""
              AND q.mime_type != "application/x.open+json"
              AND q.mime_type != "application/x.words+json"
        ');

        $sth->execute();
        $answers = $sth->fetchAll();
        $this->log(count($answers).' answers to process.');
        foreach ($answers as $index => $answer) {
            $newData = null;

            // Calculate new data string (it's the json_encode of the data structure transferred in the API)
            switch ($answer['mime_type']) {
                case 'application/x.choice+json':
                    $answerData = explode(';', $answer['data']);

                    // Filter empty elements and convert ids into uuids
                    $newData = array_map(function ($choice) use ($choices) {
                        return $this->getAnswerPartUuid($choice, $choices);
                    }, array_filter($answerData, function ($part) {
                        return !empty($part);
                    }));

                    break;

                case 'application/x.match+json':
                case 'application/x.set+json':
                    if ('application/x.set+json' === $answer['mime_type']) {
                        $propNames = ['itemId', 'setId'];
                    } else {
                        $propNames = ['firstId', 'secondId'];
                    }

                    // Get each association
                    $answerData = explode(';', $answer['data']);

                    // Filter empty elements
                    $answerData = array_filter($answerData, function ($part) {
                        return !empty($part);
                    });

                    $newData = array_map(function ($association) use ($propNames, $labels, $proposals) {
                        $associationData = explode(',', $association);

                        $data = null;

                        // The new system only allows complete association in answers
                        if (!empty($associationData) && 2 === count($associationData)) {
                            $data = new \stdClass();

                            // Convert ids into uuids
                            $data->{$propNames[0]} = $this->getAnswerPartUuid($associationData[0], $proposals);
                            $data->{$propNames[1]} = $this->getAnswerPartUuid($associationData[1], $labels);
                        }

                        return $data;
                    }, $answerData);

                    break;

                case 'application/x.pair+json':
                    // Get each pair
                    $answerData = explode(';', $answer['data']);

                    // Filter empty elements
                    $answerData = array_filter($answerData, function ($part) {
                        return !empty($part);
                    });

                    $newData = array_map(function ($pair) use ($labels, $proposals) {
                        $pairData = explode(',', $pair);

                        // Convert ids into uuids
                        if (!empty($pairData) && 2 === count($pairData)) {
                            return [
                                $this->getAnswerPartUuid($pairData[0], $proposals),
                                $this->getAnswerPartUuid($pairData[1], $labels),
                            ];
                        } else {
                            return null;
                        }
                    }, $answerData);

                    break;

                case 'application/x.cloze+json':
                    // Replace hole ids by uuids
                    $answerData = json_decode($answer['data'], true);

                    $newData = [];
                    foreach ($answerData as $position => $answerText) {
                        if (!empty($answerText)) {
                            $answeredHole = null;
                            $oldAnswer = null;
                            if (is_array($answerText)) {
                                // Format = [{"holeId":"61","answerText":""},{"holeId":"62","answerText":""}]

                                // Retrieve answered hole
                                $answeredHole = $this->getAnswerPart($answerText['holeId'], $holes);

                                // Get answer
                                $oldAnswer = $answerText['answerText'];
                            } else {
                                // Format = {"1":"travail","2":"salaire"}

                                // Retrieve answered hole
                                foreach ($holes as $hole) {
                                    if ((int) $hole['position'] === (int) $position
                                        && $hole['question_id'] === $answer['question_id']) {
                                        $answeredHole = $hole;
                                        break;
                                    }
                                }

                                // Get answer
                                $oldAnswer = $answerText;
                            }

                            if (!empty($answeredHole) && !empty($oldAnswer)) {
                                $hole = new \stdClass();
                                $hole->holeId = $answeredHole['uuid'];

                                if (!$answeredHole['selector']) {
                                    $hole->answerText = $oldAnswer;
                                } else {
                                    // replace keyword id by its value
                                    $keyword = $this->getAnswerPart($oldAnswer, $keywords);
                                    $hole->answerText = $keyword['response'];
                                }

                                $newData[] = $hole;
                            }
                        }
                    }

                    break;

                case 'application/x.graphic':
                    // Get each area
                    $answerData = explode(';', $answer['data']);

                    // Filter empty elements
                    $answerData = array_filter($answerData, function ($part) {
                        return !empty($part);
                    });

                    $newData = array_map(function ($coords) {
                        $coordsData = explode(',', $coords);

                        if (!empty($coordsData) && 2 === count($coordsData)) {
                            return [
                                $coordsData[0],
                                $coordsData[1],
                            ];
                        } else {
                            return null;
                        }
                    }, $answerData);
                    break;

                default:
                    break;
            }

            // Update answer data
            $sth = $this->connection->prepare('
                UPDATE ujm_response SET `response` = :data WHERE id = :id 
            ');

            $insertData = null;
            if (!empty($newData)) {
                $insertData = json_encode(
                    array_filter($newData, function ($data) {
                        return !empty($data);
                    })
                );
            }

            $sth->execute([
                'id' => $answer['answerId'],
                'data' => $insertData,
            ]);

            if ($index % 200 === 0) {
                $this->log('200 answers processed.');
            }
        }

        $this->log('done !');
    }

    private function getAnswerPart($id, array $parts)
    {
        $found = null;
        foreach ($parts as $part) {
            if ($part['id'] === $id) {
                $found = $part;
                break;
            }
        }

        return $found;
    }

    private function getAnswerPartUuid($id, array $parts)
    {
        $part = $this->getAnswerPart($id, $parts);
        if (!empty($part)) {
            return $part['uuid'];
        }

        return null;
    }

    private function cleanOldPairQuestions()
    {
        $this->log('Removes old pair questions data...');

        // Delete old questions
        $sth = $this->connection->prepare('
            SET FOREIGN_KEY_CHECKS = false;
            
            DELETE m FROM ujm_interaction_matching AS m
            JOIN ujm_question AS q ON (m.question_id = q.id)
            WHERE q.mime_type = "application/x.pair+json";
            
            SET FOREIGN_KEY_CHECKS = true;
        ');
        $sth->execute();

        // Delete old labels
        $sth = $this->connection->prepare('
            SET FOREIGN_KEY_CHECKS = false;
            
            DELETE l FROM ujm_label AS l
            LEFT JOIN ujm_interaction_matching AS m ON (l.interaction_matching_id = m.id)
            WHERE m.id IS NULL;
            
            SET FOREIGN_KEY_CHECKS = true;
        ');
        $sth->execute();

        // Delete old proposals
        $sth = $this->connection->prepare('
            SET FOREIGN_KEY_CHECKS = false;
            
            DELETE l FROM ujm_proposal AS p
            LEFT JOIN ujm_interaction_matching AS m ON (p.interaction_matching_id = m.id)
            WHERE m.id IS NULL;
            
            SET FOREIGN_KEY_CHECKS = true;
        ');
        $sth->execute();

        // Delete old labels/proposals association
        $sth = $this->connection->prepare('
            SET FOREIGN_KEY_CHECKS = false;
            
            DELETE l FROM ujm_proposal_label AS pl
            LEFT JOIN ujm_proposal AS p ON (pl.proposal_id = p.id)
            LEFT JOIN ujm_label AS l ON (pl.label_id = l.id)
            WHERE p.id IS NULL 
               OR l.id IS NULL;
                
            SET FOREIGN_KEY_CHECKS = true;
        ');
        $sth->execute();

        $this->log('done !');
    }

    /**
     * Updates papers data.
     *
     * - Dump the full exercise definition in `structure`
     * - Move hints to answers
     */
    private function updatePapers()
    {
        $this->log('Update Papers structures and hints...');

        $oldHints = $this->fetchHints();

        $questions = $this->om->getRepository('UJMExoBundle:Item\Item')->findAll();
        $decodedQuestions = [];

        $papers = $this->om->getRepository('UJMExoBundle:Attempt\Paper')->findAll();

        $this->om->startFlushSuite();

        $this->log(count($papers).' papers to process.');

        /** @var Paper $paper */
        foreach ($papers as $i => $paper) {
            // Checks the format of the structure to know if it has already been transformed
            $structure = $paper->getStructure();
            if (substr($structure, 0, 1) !== '{') {
                // The structure is not a JSON (this is a little bit hacky)
                // Update structure
                $this->updatePaperStructure($paper, $questions, $decodedQuestions);

                // Update hints
                $this->updatePaperHints($paper, $oldHints);

                $this->om->persist($paper);
            }

            if ($i % 200 === 0) {
                $this->om->forceFlush();
                $this->log('200 papers processed.');
            }
        }

        $this->om->endFlushSuite();

        $this->log('done !');
    }

    private function updatePaperStructure(Paper $paper, array $questions, array $decodedQuestions)
    {
        $this->log('Update structure for paper: '.$paper->getId());

        $this->log('Serialize quiz definition...');
        $quizDef = $this->exerciseSerializer->serialize($paper->getExercise(), [Transfer::INCLUDE_SOLUTIONS]);

        // Replace steps and questions by the one from paper
        $this->log('Rebuild paper structure');
        $stepsToKeep = [];
        $questionIds = explode(';', $paper->getStructure());
        foreach ($questionIds as $index => $questionId) {
            if (empty($questionId)) {
                unset($questionIds[$index]);
                continue;
            }

            $question = $this->pullQuestion($questionId, $questions, $decodedQuestions);
            if ($question) {
                // Find in which step this question is
                foreach ($quizDef->steps as $step) {
                    foreach ($step->items as $item) {
                        if ($question->id === $item->id) {
                            // Current question is part of the step
                            if (empty($stepsToKeep[$step->id])) {
                                // First time we get this step => stack the definition and reset items
                                $stepsToKeep[$step->id] = clone $step;
                                $stepsToKeep[$step->id]->items = [];
                            }

                            $stepsToKeep[$step->id]->items[] = $question;

                            unset($questionIds[$index]); // This will permits to retrieve orphan questions

                            break 2;
                        }
                    }
                }
            }
        }

        // Override quiz def with only the picked steps for the attempt
        $quizDef->steps = array_values($stepsToKeep);

        if (!empty($questionIds)) {
            $this->log('Process deleted questions...');
            // There are questions that are no longer linked to the exercise
            // Create a default step and add all
            $stepForOrphans = $this->stepSerializer->serialize(new Step(), [Transfer::INCLUDE_SOLUTIONS]);

            foreach ($questionIds as $questionId) {
                /** @var Item $question */
                $question = $this->pullQuestion($questionId, $questions, $decodedQuestions);
                if ($question) {
                    $stepForOrphans->items[] = $question;
                }
            }

            $quizDef->steps[] = $stepForOrphans;
        }

        if (Recurrence::ONCE === $quizDef->parameters->randomPick || Recurrence::ONCE === $quizDef->parameters->randomOrder) {
            // We invalidate papers for exercise that are configured to reuse old attempts structure to generate new ones
            // The generator assumes the old data still are in the exercise
            // As we don't know if this is true for migrated data, we invalidate it to avoid possible bugs
            $this->log('Invalidate paper...');
            $paper->setInvalidated(true);
        }

        $paper->setStructure(json_encode($quizDef));
    }

    private function updateClozeQuestions()
    {
        // Get cloze questions
        $sth = $this->connection->prepare(
            'SELECT * FROM ujm_interaction_hole WHERE originalText IS NULL'
        );

        $sth->execute();
        $questions = $sth->fetchAll();
        foreach ($questions as $question) {
            $holeSth = $this->connection->prepare('
                SELECT * FROM ujm_hole WHERE interaction_hole_id = :id
            ');

            $holeSth->execute([
                'id' => $question['id'],
            ]);
            $holes = $holeSth->fetchAll();

            $text = $this->replaceHoles(
                $question['htmlWithoutValue'],
                '/<select\s*id=\s*[\'|"]+([0-9]+)[\'|"]+\s*class=\s*[\'|"]+blank[\'|"]+.*[^<\/\s*select\s*>]*<\/select>/',
                $holes
            );
            // Replace inputs
            $text = $this->replaceHoles(
                $text,
                '/<input\s*id=\s*[\'|"]+([0-9]+)[\'|"]+\s*class=\s*[\'|"]+blank[\'|"]+\s*[^\/+>]*\/>/',
                $holes
            );

            // Replace selects

            $sth = $this->connection->prepare('
                UPDATE ujm_interaction_hole 
                SET htmlWithoutValue = :text, originalText = :originalText
                WHERE question_id = :id
            ');
            $sth->execute([
                'id' => $question['question_id'],
                'text' => $text,
                'originalText' => $question['htmlWithoutValue'],
            ]);
        }
    }

    private function replaceHoles($text, $searchExpr, array $holes)
    {
        $matches = [];
        if (preg_match_all($searchExpr, $text, $matches)) {
            foreach ($matches[0] as $inputIndex => $inputMatch) {
                $position = $matches[1][$inputIndex];
                foreach ($holes as $hole) {
                    if ((int) $hole['position'] === (int) $position) {
                        $text = str_replace($inputMatch, '[['.$hole['uuid'].']]', $text);
                        break;
                    }
                }
            }
        }

        return $text;
    }

    /**
     * Retrieves a question, serializes it and moves it in the decoded list (for later use)
     * before returning the serialized version of the found question.
     *
     * @param $questionId
     * @param array $questions
     * @param array $decodedQuestions
     *
     * @return \stdClass|null
     */
    private function pullQuestion($questionId, array $questions, array $decodedQuestions = [])
    {
        if (empty($decodedQuestions[$questionId])) {
            foreach ($questions as $index => $question) {
                if ($question->getId() === (int) $questionId && !empty($question->getMimeType())) {
                    $decodedQuestions[$questionId] = $this->itemSerializer->serialize($question, [Transfer::INCLUDE_SOLUTIONS]);
                    unset($questions[$index]);
                    break;
                }
            }
        }

        return !empty($decodedQuestions[$questionId]) ? $decodedQuestions[$questionId] : null;
    }

    private function updatePaperHints(Paper $paper, array $oldHints = [])
    {
        $this->log('Update hints for paper: '.$paper->getId());

        $hints = $this->pullHint($paper->getId(), $oldHints);
        foreach ($hints as $hint) {
            $answer = $paper->getAnswer($hint);
            if (empty($answer)) {
                $answer = new Answer();
                $answer->setIp('127.0.0.1'); // localhost IP. this is not a deal because it's only used to block answer submission
                $answer->setScore(0); // Score is 0 because the old notation system do not allow negative scores
                $answer->setQuestionId($hint['question_id']);

                $paper->addAnswer($answer);
            }

            $answer->addUsedHint($hint['hint_id']);
        }
    }

    private function fetchHints()
    {
        $sth = $this->connection->prepare('
            SELECT lhp.paper_id, h.uuid AS hint_id, q.uuid AS question_id
            FROM ujm_link_hint_paper AS lhp
            LEFT JOIN ujm_hint AS h ON (lhp.hint_id = h.id AND h.id IS NOT NULL)
            LEFT JOIN ujm_question AS q ON (h.question_id = q.id AND q.id IS NOT NULL)
        ');

        $sth->execute();

        return $sth->fetchAll();
    }

    private function pullHint($paperId, array $hints = [])
    {
        $paperHints = [];

        foreach ($hints as $index => $hint) {
            if ($paperId === $hint['paper_id']) {
                $paperHints = $hint;
                unset($hints[$index]);
            }
        }

        return $paperHints;
    }
}
