<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\ResultBundle\Entity\Mark;
use Claroline\ResultBundle\Entity\Result;
use Claroline\ResultBundle\Event\Log\LogResultsDeleteMarkEvent;
use Claroline\ResultBundle\Event\Log\LogResultsNewMarkEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @DI\Service("claroline.result.result_manager")
 */
class ResultManager
{
    const ERROR_EMPTY_CSV = 0;
    const ERROR_MISSING_VALUES = 1;
    const ERROR_INVALID_MARK = 2;
    const ERROR_EMPTY_VALUES = 3;
    const ERROR_EXTRA_USERS = 4;

    private $om;
    private $templating;
    private $utils;
    private $dispatcher;
    private $logRepository;

    /**
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "templating"     = @DI\Inject("templating"),
     *     "utils"          = @DI\Inject("claroline.utilities.misc"),
     *     "dispatcher"    = @DI\Inject("event_dispatcher")
     * })
     *
     * @param ObjectManager            $om
     * @param EngineInterface          $templating
     * @param ClaroUtilities           $utils
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        ObjectManager $om,
        EngineInterface $templating,
        ClaroUtilities $utils,
        EventDispatcherInterface $dispatcher
    ) {
        $this->om = $om;
        $this->templating = $templating;
        $this->utils = $utils;
        $this->dispatcher = $dispatcher;
        $this->logRepository = $om->getRepository('ClarolineCoreBundle:Log\Log');
    }

    /**
     * Creates a result resource.
     *
     * @param Result $result
     *
     * @return Result
     */
    public function create(Result $result)
    {
        $this->om->persist($result);
        $this->om->flush();

        return $result;
    }

    /**
     * Deletes a result resource.
     *
     * @param Result $result
     */
    public function delete(Result $result)
    {
        $this->om->remove($result);
        $this->om->flush();
    }

    /**
     * Returns the content of the result widget for a given user/workspace combination.
     *
     * @param Workspace $workspace
     * @param User      $user
     *
     * @return string
     */
    public function getWidgetContent(Workspace $workspace = null, User $user = null)
    {
        $results = [];

        if (!is_null($user)) {
            $results = is_null($workspace) ?
                $this->om->getRepository('ClarolineResultBundle:Result')->findByUser($user) :
                $this->om->getRepository('ClarolineResultBundle:Result')->findByUserAndWorkspace($user, $workspace);
        }

        return $this->templating->render('ClarolineResultBundle:Result:widget.html.twig', [
            'results' => $results,
        ]);
    }

    /**
     * Returns the content of the result resource form.
     *
     * @param FormView $view
     *
     * @return string
     */
    public function getResultFormContent(FormView $view)
    {
        return $this->templating->render(
             'ClarolineCoreBundle:Resource:createForm.html.twig',
             [
                 'form' => $view,
                 'resourceType' => 'claroline_result',
             ]
         );
    }

    /**
     * Returns an array representation of the marks associated with a
     * result. If the user passed in has the permission to edit the result,
     * all the marks are returned, otherwise only his mark is returned.
     *
     * @param Result $result
     * @param User   $user
     * @param bool   $canEdit
     *
     * @return array
     */
    public function getMarks(Result $result, User $user, $canEdit)
    {
        $repo = $this->om->getRepository('ClarolineResultBundle:Mark');

        return $canEdit ?
            $repo->findByResult($result) :
            $repo->findByResultAndUser($result, $user);
    }

    /**
     * Returns an array representation of the members of the workspace
     * in which the given result lives. If the edit flag is set to false,
     * an empty array is returned.
     *
     * @param Result $result
     * @param bool   $canEdit
     *
     * @return array
     */
    public function getUsers(Result $result, $canEdit)
    {
        if (!$canEdit) {
            return [];
        }

        $repo = $this->om->getRepository('ClarolineCoreBundle:User');
        $roles = $result->getResourceNode()->getWorkspace()->getRoles()->toArray();
        $users = $repo->findUsersByRolesIncludingGroups($roles);

        return array_map(function ($user) {
            return [
                'id' => $user->getId(),
                'name' => "{$user->getFirstName()} {$user->getLastName()}",
            ];
        }, $users);
    }

    /**
     * Returns whether a mark is valid.
     *
     * @param Result $result
     * @param mixed  $mark
     *
     * @return bool
     */
    public function isValidMark(Result $result, $mark)
    {
        // normalize french decimal marks
        $mark = str_replace(',', '.', $mark);

        return is_numeric($mark) && (float) $mark <= $result->getTotal();
    }

    /**
     * Creates a new mark.
     *
     * @param Result $result
     * @param User   $user
     * @param string $mark
     *
     * @return mark
     */
    public function createMark(Result $result, User $user, $mark)
    {
        $mark = new Mark($result, $user, $mark);
        $this->om->persist($mark);
        $this->om->flush();

        $newMarkEvent = new LogResultsNewMarkEvent($mark);
        $this->dispatcher->dispatch('log', $newMarkEvent);

        return $mark;
    }

    /**
     * Deletes a mark.
     *
     * @param Mark $mark
     */
    public function deleteMark(Mark $mark)
    {
        $this->om->remove($mark);
        $this->om->flush();
        // First of all update older mark events so as result would be 0
        $this->updateNewMarkEventResult($mark->getUser(), $mark->getId(), 0);
        // Then delete mark
        $deleteMarkEvent = new LogResultsDeleteMarkEvent($mark);
        $this->dispatcher->dispatch('log', $deleteMarkEvent);
    }

    /**
     * Updates a mark.
     *
     * @param Mark   $mark
     * @param string $value
     */
    public function updateMark(Mark $mark, $value)
    {
        $oldMark = $mark->getValue();
        $mark->setValue($value);
        $this->om->flush();
        // First of all update older mark events so as result would be new value
        $this->updateNewMarkEventResult($mark->getUser(), $mark->getId(), $value);
        // Then create new mark event to log update
        $newMarkEvent = new LogResultsNewMarkEvent($mark, $oldMark);
        $this->dispatcher->dispatch('log', $newMarkEvent);
    }

    /**
     * Import marks from a CSV file into a result resource.
     *
     * @param Result       $result
     * @param UploadedFile $csvFile
     * @param string       $importType Either "fullname", "code" or "username"
     *
     * @return array
     */
    public function importMarksFromCsv(Result $result, UploadedFile $csvFile, $importType = 'fullname')
    {
        $repo = $this->om->getRepository('ClarolineCoreBundle:User');
        $roles = $result->getResourceNode()->getWorkspace()->getRoles()->toArray();
        $users = $repo->findUsersByRolesIncludingGroups($roles);

        $data = [
            'marks' => [],
            'errors' => [],
        ];

        $fileData = file_get_contents($csvFile);
        $fileData = $this->utils->formatCsvOutput($fileData);
        $lines = str_getcsv($fileData, PHP_EOL);

        if (1 === count($lines) && null === $lines[0]) {
            $data['errors'][] = [
                'code' => self::ERROR_EMPTY_CSV,
                'message' => 'errors.csv_empty',
                'line' => null,
            ];

            return $data;
        }

        $countRowEl = 'fullname' === $importType ? 3 : 2;

        foreach (file($csvFile->getPathname()) as $index => $line) {
            $values = array_map('trim', str_getcsv($line, ';'));
            $lineNumber = $index + 1;

            if (count($values) < $countRowEl) {
                $data['errors'][] = [
                    'code' => self::ERROR_MISSING_VALUES,
                    'message' => 'errors.csv_missing_values',
                    'line' => $lineNumber,
                ];
            } elseif (in_array('', $values)) {
                $data['errors'][] = [
                    'code' => self::ERROR_EMPTY_VALUES,
                    'message' => 'errors.csv_empty_values',
                    'line' => $lineNumber,
                ];
            } elseif (!$this->isValidMark($result, $values[$countRowEl - 1])) {
                $data['errors'][] = [
                    'code' => self::ERROR_INVALID_MARK,
                    'message' => 'errors.invalid_mark',
                    'line' => $lineNumber,
                ];
            } else {
                $matchedUser = false;

                foreach ($users as $user) {
                    switch ($importType) {
                        case 'fullname':
                            if ($user->getFirstName() === $values[0] && $user->getLastName() === $values[1]) {
                                $matchedUser = $user;
                                break 2;
                            }
                            // no break
                        case 'code':
                            if ($user->getAdministrativeCode() === $values[0]) {
                                $matchedUser = $user;
                                break 2;
                            }
                            // no break
                        case 'username':
                            if ($user->getUsername() === $values[0]) {
                                $matchedUser = $user;
                                break 2;
                            }
                    }
                }

                if (!$matchedUser) {
                    $data['errors'][] = [
                        'code' => self::ERROR_EXTRA_USERS,
                        'message' => 'errors.csv_extra_users',
                        'line' => $lineNumber,
                    ];
                } else {
                    $mark = new Mark($result, $user, $values[$countRowEl - 1]);
                    $this->om->persist($mark);
                    $this->om->flush();
                    $data['marks'][] = $mark;
                    //Create log for mark
                    $newMarkEvent = new LogResultsNewMarkEvent($mark);
                    $this->dispatcher->dispatch('log', $newMarkEvent);
                }
            }
        }

        return $data;
    }

    private function updateNewMarkEventResult(User $receiver, $markId, $newValue)
    {
        $logs = $this->logRepository->findBy([
            'receiver' => $receiver,
            'action' => LogResultsNewMarkEvent::ACTION,
        ]);
        $updatedCnt = 0;
        foreach ($logs as $log) {
            $details = $log->getDetails();
            if ($details['mark']['id'] === $markId) {
                $details['result'] = $newValue;
                $log->setDetails($details);
                $this->om->persist($log);
                $updatedCnt += 1;
            }
        }
        if ($updatedCnt > 0) {
            $this->om->flush();
        }
    }
}
