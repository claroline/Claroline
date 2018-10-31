<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Tool;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Service
 */
class ProgressionListener
{
    /** @var FinderProvider */
    private $finder;

    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;

    /** @var SerializerProvider */
    private $serializer;

    /** @var TwigEngine */
    private $templating;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * ProgressionListener constructor.
     *
     * @DI\InjectParams({
     *     "finder"              = @DI\Inject("claroline.api.finder"),
     *     "resourceEvalManager" = @DI\Inject("claroline.manager.resource_evaluation_manager"),
     *     "serializer"          = @DI\Inject("claroline.api.serializer"),
     *     "templating"          = @DI\Inject("templating"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage")
     * })
     *
     * @param FinderProvider            $finder
     * @param ResourceEvaluationManager $resourceEvalManager
     * @param SerializerProvider        $serializer
     * @param TwigEngine                $templating
     * @param TokenStorageInterface     $tokenStorage
     */
    public function __construct(
        FinderProvider $finder,
        ResourceEvaluationManager $resourceEvalManager,
        SerializerProvider $serializer,
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage
    ) {
        $this->finder = $finder;
        $this->resourceEvalManager = $resourceEvalManager;
        $this->serializer = $serializer;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Displays workspace progression tool.
     *
     * @DI\Observe("open_tool_workspace_progression")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspace(DisplayToolEvent $event)
    {
        $workspace = $event->getWorkspace();
        $user = $this->tokenStorage->getToken()->getUser();

        if ('anon.' === $user) {
            throw new AccessDeniedException();
        }
        $workspaceRoot = $this->finder->get(ResourceNode::class)->find([
            'workspace' => $workspace->getUuid(),
            'parent' => null,
        ])[0];

        $roles = $user->getRoles();
        $filters = [
            'active' => true,
            'published' => true,
            'hidden' => false,
            'resourceTypeEnabled' => true,
            'workspace' => $workspace->getUuid(),
        ];

        if (!in_array('ROLE_ADMIN', $roles)) {
            $filters['roles'] = $roles;
        }
        // Get all resource nodes available for current user in the workspace
        $nodes = $this->finder->get(ResourceNode::class)->find($filters);
        $filters['parent'] = $workspaceRoot;
        // Get all root resource nodes available for current user in the workspace
        $rootNodes = $this->finder->get(ResourceNode::class)->find($filters);

        $items = $this->formatNodes($user, $rootNodes, $nodes);

        $content = $this->templating->render(
            'ClarolineCoreBundle:tool:progression.html.twig', [
                'workspace' => $workspace,
                'context' => [
                    'type' => 'workspace',
                    'data' => $this->serializer->serialize($workspace),
                ],
                'items' => $items,
                'levelMax' => 1,    // how deep to process children recursively
            ]
        );

        $event->setContent($content);
        $event->stopPropagation();
    }

    private function formatNodes(User $user, array $rootNodes, array $nodes)
    {
        $items = [];
        $nodesArray = [];

        foreach ($nodes as $node) {
            $nodesArray[$node->getUuid()] = $node;
        }
        foreach ($rootNodes as $node) {
            $evaluation = $this->resourceEvalManager->getResourceUserEvaluation($node, $user, false);
            $item = $this->serializer->serialize($node, [Options::SERIALIZE_MINIMAL, Options::IS_RECURSIVE]);
            $item['level'] = 0;
            $item['openingUrl'] = ['claro_resource_show_short', ['id' => $item['id']]];
            $item['validated'] = !is_null($evaluation) && 0 < $evaluation->getNbOpenings();
            $items[] = $item;

            if (isset($item['children']) && 0 < count($item['children'])) {
                foreach ($item['children'] as $child) {
                    if (isset($nodesArray[$child['id']])) {
                        $childEval = $this->resourceEvalManager->getResourceUserEvaluation($nodesArray[$child['id']], $user, false);
                        $childItem = $child;
                        $childItem['level'] = 1;
                        $childItem['openingUrl'] = ['claro_resource_show_short', ['id' => $childItem['id']]];
                        $childItem['validated'] = !is_null($childEval) && 0 < $childEval->getNbOpenings();
                        $items[] = $childItem;
                    }
                }
            }
        }

        return $items;
    }
}
