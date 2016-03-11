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
use Claroline\ResultBundle\Entity\Result;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormView;

/**
 * @DI\Service("claroline.result.result_manager")
 */
class ResultManager
{
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
        return [];

//        $results = $this->om->getRepository('ClarolineResultBundle:Result')
//
//        $repo = $this->om->getRepository('ClarolineCoreBundle:User');
//
//
//        return $canEdit ?
//            $repo->findUsersByWorkspace($result->getResourceNode()->getWorkspace()) :
//            [];
    }
}
