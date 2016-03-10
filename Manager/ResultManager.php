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
}
