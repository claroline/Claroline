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
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Form\ActivityParametersType;
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
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ActivityController
{
    private $securityContext;
    private $formFactory;
    private $request;
    private $activityManager;
    private $resourceManager;
    private $translator;

    /**
     * @InjectParams({
     *     "securityContext"    = @Inject("security.context"),
     *     "formFactory"        = @Inject("form.factory"),
     *     "request"            = @Inject("request_stack"),
     *     "activityManager"    = @Inject("claroline.manager.activity_manager"),
     *     "resourceManager"    = @Inject("claroline.manager.resource_manager"),
     *     "translator"         = @Inject("translator")
     * })
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        RequestStack $request,
        ActivityManager $activityManager,
        ResourceManager $resourceManager,
        TranslatorInterface $translator
    )
    {
        $this->securityContext = $securityContext;
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

        $form = $this->formFactory->create(new ActivityType, $resource);
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

        if ($form->isValid()
            && $formParams->isValid()
            && ($formRule->get('action')->getData() === 'none' || $formRule->isValid())) {

            $this->activityManager->editActivity($form->getData());

            $maxDuration = $formParams->get('max_duration')->getData();
            $maxAttempts = $formParams->get('max_attempts')->getData();
            $evaluationType = $formParams->get('evaluation_type')->getData();

            $this->activityManager->updateParameters(
                $params,
                $maxDuration,
                $maxAttempts,
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
                $activeFrom = $formRule->get('activeFrom')->getData();
                $activeUntil = $formRule->get('activeUntil')->getData();

                if ($hasRule) {
                    $this->activityManager->updateActivityRule(
                        $rule,
                        $action,
                        $occurrence,
                        $result,
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
                        $activeFrom,
                        $activeUntil,
                        $resourceNode
                    );
                }
            }
        }

        return array(
            '_resource' => $resource,
            'form' => $form->createView(),
            'formParams' => $formParams->createView(),
            'params' => $params,
            'formRule' => $formRule->createView()
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
                        'type' => $resource->getResourceType(),
                        'mimeType' => $resource->getMimeType(),
                    )
                )
            );
        }

        return new Response('false');
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
     * @param string $action
     * @param int $occurrence
     * @param string $result
     * @param datetime $activeFrom
     * @param datetime $activeUntil
     * @param ResourceNode $resourceNode
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
    )
    {
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
    public function getRuleActionsFromResourceType($resourceTypeName)
    {
        $resourceType = $this->resourceManager
            ->getResourceTypeByName($resourceTypeName);

        $ruleActions = $this->activityManager
            ->getRuleActionsByResourceType($resourceType);

        if (count($ruleActions) > 0) {
            $actions = array();

            foreach ($ruleActions as $ruleAction) {
                $actions[] = $ruleAction->getAction();
            }

            return new Response(json_encode($actions));
        }

        return new Response('false');
    }

    private function checkAccess($permission, $resource)
    {
        if (!$this->securityContext->isGranted($permission, $resource)) {
            throw new AccessDeniedException();
        }
    }
}
