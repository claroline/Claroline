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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\ResultBundle\Entity\Mark;
use Claroline\ResultBundle\Entity\Result;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
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

    /**
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "templating" = @DI\Inject("templating")
     * })
     *
     * @param ObjectManager     $om
     * @param EngineInterface   $templating
     */
    public function __construct(ObjectManager $om, EngineInterface $templating)
    {
        $this->om = $om;
        $this->templating = $templating;
    }

    /**
     * Creates a result resource.
     *
     * @param Result $result
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
     * @return string
     */
    public function getWidgetContent(Workspace $workspace, User $user)
    {
        $results = $this->om->getRepository('ClarolineResultBundle:Result')
            ->findByUserAndWorkspace($user, $workspace);

        return $this->templating->render('ClarolineResultBundle:Result:widget.html.twig', [
            'results' => $results
        ]);
    }

    /**
     * Returns the content of the result resource form.
     *
     * @param FormView $view
     * @return string
     */
    public function getResultFormContent(FormView $view)
    {
         return $this->templating->render(
             'ClarolineCoreBundle:Resource:createForm.html.twig',
             [
                 'form' => $view,
                 'resourceType' => 'claroline_result'
             ]
         );
    }

    /**
     * Returns an array representation of the marks associated with a
     * result. If the user passed in has the permission to edit the result,
     * all the marks are returned, otherwise only his mark is returned.
     *
     * @param Result    $result
     * @param User      $user
     * @param bool      $canEdit
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
     * @param Result    $result
     * @param bool      $canEdit
     * @return array
     */
    public function getUsers(Result $result, $canEdit)
    {
        if (!$canEdit) {
            return [];
        }

        $repo = $this->om->getRepository('ClarolineCoreBundle:User');
        $users = $repo->findUsersByWorkspace($result->getResourceNode()->getWorkspace());

        return array_map(function ($user) {
            return [
                'id' => $user->getId(),
                'name' => "{$user->getFirstName()} {$user->getLastName()}"
            ];
        }, $users);
    }

    /**
     * Returns whether a mark is valid.
     *
     * @param Result    $result
     * @param mixed     $mark
     * @return bool
     */
    public function isValidMark(Result $result, $mark)
    {
        $total = $result->getTotal();
        $intMark = (int) $mark;

        return is_numeric($mark) && is_int($intMark) && $intMark <= $total;
    }

    /**
     * Creates a new mark.
     *
     * @param Result    $result
     * @param User      $user
     * @param string    $mark
     * @return mark
     */
    public function createMark(Result $result, User $user, $mark)
    {
        $mark = new Mark($result, $user, $mark);
        $this->om->persist($mark);
        $this->om->flush();

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
    }

    /**
     * Updates a mark.
     *
     * @param Mark      $mark
     * @param string    $value
     */
    public function updateMark(Mark $mark, $value)
    {
        $mark->setValue($value);
        $this->om->flush();
    }

    public function importMarksFromCsv(Result $result, UploadedFile $csvFile)
    {
        $repo = $this->om->getRepository('ClarolineCoreBundle:User');
        $users = $repo->findUsersByWorkspace($result->getResourceNode()->getWorkspace());

        $data = [
            'marks' => [],
            'errors' => []
        ];

        $lines = file($csvFile->getPathname());

        if (count($lines) === 0) {
            $data['errors'][] = [
                'code' => self::ERROR_EMPTY_CSV,
                'message' => 'errors.csv_empty',
                'line' => null
            ];

            return $data;
        }

        foreach (file($csvFile->getPathname()) as $index => $line) {
            $values = array_map('trim', str_getcsv($line));
            $lineNumber = $index + 1;

            if (count($values) < 3) {
                $data['errors'][] = [
                    'code' => self::ERROR_MISSING_VALUES,
                    'message' => 'errors.csv_missing_values',
                    'line' => $lineNumber
                ];
            } elseif (in_array('', $values)) {
                $data['errors'][] = [
                    'code' => self::ERROR_EMPTY_VALUES,
                    'message' => 'errors.csv_empty_values',
                    'line' => $lineNumber
                ];
            } elseif (!$this->isValidMark($result, $values[2])) {
                $data['errors'][] = [
                    'code' => self::ERROR_INVALID_MARK,
                    'message' => 'errors.invalid_mark',
                    'line' => $lineNumber
                ];
            } else {
                $matchedUser = false;

                foreach ($users as $user) {
                    if ($user->getFirstName() === $values[0] && $user->getLastName() === $values[1]) {
                        $matchedUser = $user;
                        break;
                    }
                }

                if (!$matchedUser) {
                    $data['errors'][] = [
                        'code' => self::ERROR_EXTRA_USERS,
                        'message' => 'errors.csv_extra_users',
                        'line' => $lineNumber
                    ];
                } else {
                    $mark = new Mark($result, $user, $values[2]);
                    $this->om->persist($mark);
                    $this->om->flush();
                    $data['marks'][] = $mark;
                }
            }
        }

        return $data;
    }
}
