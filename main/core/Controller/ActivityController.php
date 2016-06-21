<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Activity\ActivityParameters;
use Claroline\CoreBundle\Entity\Activity\ActivityRule;
use Claroline\CoreBundle\Entity\Activity\Evaluation;
use Claroline\CoreBundle\Entity\Activity\PastEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Form\ActivityEvaluationType;
use Claroline\CoreBundle\Form\ActivityParametersType;
use Claroline\CoreBundle\Form\ActivityPastEvaluationType;
use Claroline\CoreBundle\Form\ActivityType;
use Claroline\CoreBundle\Form\ActivityRuleType;
use Claroline\CoreBundle\Manager\ActivityManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ActivityController
{
    private $authorization;
    private $formFactory;
    private $request;
    private $activityManager;
    private $resourceManager;
    private $translator;

    /**
     * @InjectParams({
     *     "authorization"    = @Inject("security.authorization_checker"),
     *     "formFactory"        = @Inject("form.factory"),
     *     "request"            = @Inject("request_stack"),
     *     "activityManager"    = @Inject("claroline.manager.activity_manager"),
     *     "resourceManager"    = @Inject("claroline.manager.resource_manager"),
     *     "translator"         = @Inject("translator")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        FormFactoryInterface $formFactory,
        RequestStack $request,
        ActivityManager $activityManager,
        ResourceManager $resourceManager,
        TranslatorInterface $translator
    ) {
        $this->authorization = $authorization;
        $this->formFactory = $formFactory;
        $this->request = $request->getMasterRequest();
        $this->activityManager = $activityManager;
        $this->resourceManager = $resourceManager;
        $this->translator = $translator;
    }

    /**
     * @Route("edit/{resource}", name="claro_activity_edit")
     * @ParamConverter("resource", class = "ClarolineCoreBundle:Resource\Activity", options = {"id" = "resource"})
     * @Template()
     */
    public function editAction($resource)
    {
        $this->checkAccess('edit', $resource);

        $params = $resource->getParameters();
        $rules = $params->getRules();
        $hasRule = false;

        if (count($rules) > 0) {
            $hasRule = true;
            $rule = $rules->first();
        } else {
            $rule = new ActivityRule();
        }

        $form = $this->formFactory->create(new ActivityType(), $resource);
        $form->handleRequest($this->request);

        $formParams = $this->formFactory->create(
            new ActivityParametersType(),
            $params
        );
        $formParams->handleRequest($this->request);

        $formRule = $this->formFactory->create(
            new ActivityRuleType($this->activityManager, $this->translator),
            $rule
        );
        $formRule->handleRequest($this->request);

        $errors = $formParams->getErrors();
        foreach ($errors as $error) {
            echo $error->getMessage();
        }

        if ($form->isValid()
            && $formParams->isValid()
            && ($formRule->get('action')->getData() === 'none' || $formRule->isValid())) {
            $this->activityManager->editActivity($form->getData());
            $evaluationType = $formParams->get('evaluation_type')->getData();

            $this->activityManager->updateParameters(
                $params,
                null,
                null,
                $evaluationType
            );

            if ($formRule->get('action')->getData() === 'none') {
                if ($hasRule) {
                    $this->activityManager->deleteActivityRule($rule);
                }
            } else {
                $primaryResource = $form->get('primaryResource')->getData();
                $resourceNode = !is_null($primaryResource) ?
                        $primaryResource :
                        null;
                $action = $formRule->get('action')->getData();
                $occurrence = $formRule->get('occurrence')->getData();
                $result = $formRule->get('result')->getData();
                $resultMax = $formRule->get('resultMax')->getData();
                $isResultVisible = $formRule->get('isResultVisible')->getData();
                $activeFrom = $formRule->get('activeFrom')->getData();
                $activeUntil = $formRule->get('activeUntil')->getData();

                if ($hasRule) {
                    $this->activityManager->updateActivityRule(
                            $rule,
                            $action,
                            $occurrence,
                            $result,
                            $resultMax,
                            $isResultVisible,
                            $activeFrom,
                            $activeUntil,
                            $resourceNode
                        );
                } else {
                    $this->activityManager->createActivityRule(
                            $params,
                            $action,
                            $occurrence,
                            $result,
                            $resultMax,
                            $isResultVisible,
                            $activeFrom,
                            $activeUntil,
                            $resourceNode
                        );
                }
            }
        }

            //set the permissions for each resources
            $this->activityManager->initializePermissions($resource);

        return array(
            '_resource' => $resource,
            'form' => $form->createView(),
            'formParams' => $formParams->createView(),
            'params' => $params,
            'formRule' => $formRule->createView(),
            'defaultRuleStartingDate' => $resource->getResourceNode()->getCreationDate()->format('Y-m-d'),
        );
    }

    /**
     * @Route("add/{activity}/{resource}", name="claro_activity_add", options = {"expose": true})
     * @ParamConverter("activity", class = "ClarolineCoreBundle:Resource\Activity", options = {"id" = "activity"})
     * @ParamConverter("resource", class = "ClarolineCoreBundle:Resource\ResourceNode", options = {"id" = "resource"})
     */
    public function addAction($activity, $resource)
    {
        $this->checkAccess('edit', $resource);

        if ($this->activityManager->addResource($activity, $resource)) {
            return new Response(
                json_encode(
                    array(
                        'id' => $resource->getId(),
                        'name' => $resource->getName(),
                        'type' => $resource->getResourceType()->getName(),
                        'mimeType' => $resource->getMimeType(),
                    )
                )
            );
        }

        return new Response('false');
    }

    /**
     * @Route("removeprimary/{activity}", name="claro_activity_remove_primary_resource", options = {"expose": true})
     * @ParamConverter("activity", class = "ClarolineCoreBundle:Resource\Activity", options = {"id" = "activity"})
     */
    public function removePrimaryResourceAction($activity)
    {
        $this->checkAccess('edit', $activity);

        $this->activityManager->removePrimaryResource($activity);

        return new Response('true');
    }

    /**
     * @Route("remove/{activity}/{resource}", name="claro_activity_remove_resource", options = {"expose": true})
     * @ParamConverter("activity", class = "ClarolineCoreBundle:Resource\Activity", options = {"id" = "activity"})
     * @ParamConverter("resource", class = "ClarolineCoreBundle:Resource\ResourceNode", options = {"id" = "resource"})
     */
    public function removeAction($activity, $resource)
    {
        $this->checkAccess('edit', $activity);

        if ($this->activityManager->removeResource($activity, $resource)) {
            return new Response('true');
        }

        return new Response('false');
    }

    /**
     * @Route(
     *     "activity/add/rule/{activityParamsId}/{action}/{occurrence}/{result}/{activeFrom}/{activeUntil}/{resourceNodeId}",
     *     name="claro_add_rule_to_activity",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     * @ParamConverter(
     *      "actvityParams",
     *      class="ClarolineCoreBundle:Activity\ActivityParameters",
     *      options={"id" = "activityParamsId", "strictId" = true}
     * )
     * @ParamConverter(
     *      "resourceNode",
     *      class="ClarolineCoreBundle:Resource\ResourceNode",
     *      options={"id" = "resourceNodeId", "strictId" = true}
     * )
     *
     * Creates a rule and associates it to the activity
     *
     * @param ActivityParameters $activityParams
     * @param string             $action
     * @param int                $occurrence
     * @param string             $result
     * @param datetime           $activeFrom
     * @param datetime           $activeUntil
     * @param ResourceNode       $resourceNode
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addRuleToActivity(
        ActivityParameters $activityParams,
        $action,
        $occurrence,
        $result,
        $activeFrom,
        $activeUntil,
        ResourceNode $resourceNode = null
    ) {
        $this->activityManager->createActivityRule(
            $activityParams,
            $action,
            $occurrence,
            $result,
            $activeFrom,
            $activeUntil,
            $resourceNode
        );
    }

    /**
     * @Route(
     *     "activity/rule/actions/resource/type/{resourceTypeName}",
     *     name="claro_get_rule_actions_from_resource_type",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Get rule action names associated to a resource type
     *
     * @param string $type
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getRuleActionsFromResourceType($resourceTypeName = null)
    {
        if (is_null($resourceTypeName)) {
            $ruleActions = $this->activityManager->getRuleActionsWithNoResourceType();
        } else {
            $resourceType = $this->resourceManager
                ->getResourceTypeByName($resourceTypeName);
            $ruleActions = $this->activityManager
                ->getRuleActionsByResourceType($resourceType);
        }

        if (count($ruleActions) > 0) {
            $actions = array();

            foreach ($ruleActions as $ruleAction) {
                $actions[] = $ruleAction->getAction();
            }

            return new Response(json_encode($actions));
        }

        return new Response('false');
    }

    /**
     * @Route(
     *     "activity/display/evaluation/parameters/{paramsId}",
     *     name="claro_display_activity_evaluation",
     *     options={"expose"=true}
     * )
     * @ParamConverter("currentUser", options={"authenticatedUser" = true})
     * @ParamConverter(
     *      "params",
     *      class="ClarolineCoreBundle:Activity\ActivityParameters",
     *      options={"id" = "paramsId", "strictId" = true}
     * )
     * @Template()
     *
     * Display evaluations of the activity for the current user
     *
     * @param User               $currentUser
     * @param ActivityParameters $params
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function displayActivityEvaluationAction(
        User $currentUser,
        ActivityParameters $params
    ) {
        $evaluation =
            $this->activityManager->getEvaluationByUserAndActivityParams($currentUser, $params) ?:
            $this->activityManager->createBlankEvaluation($currentUser, $params);
        $pastEvals = $this->activityManager
            ->getPastEvaluationsByUserAndActivityParams($currentUser, $params);
        $ruleScore = null;
        $isResultVisible = false;

        if ($params->getEvaluationType() === 'automatic' &&
            count($params->getRules()) > 0) {
            $rule = $params->getRules()->first();
            $score = $rule->getResult();
            $scoreMax = $rule->getResultMax();

            if (!is_null($score)) {
                $ruleScore = $score;

                if (!is_null($scoreMax)) {
                    $ruleScore .= ' / '.$scoreMax;
                }

                $ruleResultVisible = $rule->getIsResultVisible();
                $isResultVisible = !empty($ruleResultVisible);
            }
        }

        return array(
            'activityParameters' => $params,
            'evaluation' => $evaluation,
            'pastEvals' => $pastEvals,
            'ruleScore' => $ruleScore,
            'isResultVisible' => $isResultVisible,
        );
    }

    /**
     * @Route(
     *     "edit/activity/evaluation/{evaluationId}",
     *     name="claro_activity_evaluation_edit",
     *     options={"expose"=true}
     * )
     * @ParamConverter("currentUser", options={"authenticatedUser" = true})
     * @ParamConverter(
     *      "evaluation",
     *      class="ClarolineCoreBundle:Activity\Evaluation",
     *      options={"id" = "evaluationId", "strictId" = true}
     * )
     * @Template()
     */
    public function editActivityEvaluationAction(
        User $currentUser,
        Evaluation $evaluation
    ) {
        $isWorkspaceManager = false;
        $activityParams = $evaluation->getActivityParameters();
        $activity = $activityParams->getActivity();

        if (!is_null($activity)) {
            $workspace = $activity->getResourceNode()->getWorkspace();
            $roleNames = $currentUser->getRoles();
            $isWorkspaceManager = $this->isWorkspaceManager($workspace, $roleNames);
        }

        if (!$isWorkspaceManager) {
            throw new AccessDeniedException();
        }

        $form = $this->formFactory
            ->create(new ActivityEvaluationType(), $evaluation);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->activityManager->editEvaluation($evaluation);

            return new Response('success', 204);
        }

        return array(
            'form' => $form->createView(),
            'evaluation' => $evaluation,
        );
    }

    /**
     * @Route(
     *     "edit/activity/past/evaluation/{pastEvaluationId}",
     *     name="claro_activity_past_evaluation_edit",
     *     options={"expose"=true}
     * )
     * @ParamConverter("currentUser", options={"authenticatedUser" = true})
     * @ParamConverter(
     *      "pastEvaluation",
     *      class="ClarolineCoreBundle:Activity\PastEvaluation",
     *      options={"id" = "pastEvaluationId", "strictId" = true}
     * )
     * @Template()
     */
    public function editActivityPastEvaluationAction(
        User $currentUser,
        PastEvaluation $pastEvaluation
    ) {
        $isWorkspaceManager = false;
        $activityParams = $pastEvaluation->getActivityParameters();
        $activity = $activityParams->getActivity();

        if (!is_null($activity)) {
            $workspace = $activity->getResourceNode()->getWorkspace();
            $roleNames = $currentUser->getRoles();
            $isWorkspaceManager = $this->isWorkspaceManager($workspace, $roleNames);
        }

        if (!$isWorkspaceManager) {
            throw new AccessDeniedException();
        }

        $form = $this->formFactory
            ->create(new ActivityPastEvaluationType(), $pastEvaluation);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->activityManager->editPastEvaluation($pastEvaluation);

            return new Response('success', 204);
        }

        return array(
            'form' => $form->createView(),
            'pastEvaluation' => $pastEvaluation,
        );
    }

    private function checkAccess($permission, $resource)
    {
        if (!$this->authorization->isGranted($permission, $resource)) {
            throw new AccessDeniedException();
        }
    }

    private function isWorkspaceManager(Workspace $workspace, array $roleNames)
    {
        $isWorkspaceManager = false;
        $managerRole = 'ROLE_WS_MANAGER_'.$workspace->getGuid();

        if (in_array('ROLE_ADMIN', $roleNames) ||
            in_array($managerRole, $roleNames)) {
            $isWorkspaceManager = true;
        }

        return $isWorkspaceManager;
    }
}
