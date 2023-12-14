<?php

namespace Claroline\CoreBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ToolSource
{
    public function __construct(
        private readonly FinderProvider $finder,
        private readonly SerializerProvider $serializer,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly WorkspaceManager $workspaceManager
    ) {
    }

    public function getData(GetDataEvent $event): void
    {
        $options = $event->getOptions();
        $user = $event->getUser();

        $roles = ['ROLE_ANONYMOUS'];
        if ($user) {
            $roles = $user->getRoles();
        }

        $workspace = $event->getWorkspace();
        if (!in_array('ROLE_ADMIN', $roles) || ($workspace && !$this->workspaceManager->isManager($workspace, $this->tokenStorage->getToken()))) {
            $options['hiddenFilters']['roles'] = $roles;
        }

        $options['hiddenFilters']['context'] = $event->getContext();
        if ($workspace) {
            $options['hiddenFilters']['contextId'] = $workspace->getUuid();
        }

        $context = [
            'type' => $event->getContext(),
            'data' => WorkspaceContext::getName() === $event->getContext() ?
                $this->serializer->serialize($event->getWorkspace(), [SerializerInterface::SERIALIZE_MINIMAL]) :
                null,
        ];

        $tools = $this->finder->search(OrderedTool::class, $options);

        $nbTools = count($tools['data']);
        for ($i = 0; $i < $nbTools; ++$i) {
            $tools['data'][$i]['context'] = $context;
        }

        $event->setData($tools);
        $event->stopPropagation();
    }
}
