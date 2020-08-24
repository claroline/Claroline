<?php

namespace Claroline\CoreBundle\API\Serializer\Widget\Type;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\Type\ResourceWidget;
use Claroline\CoreBundle\Repository\ResourceNodeRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ResourceWidgetSerializer
{
    use SerializerTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;
    /** @var ResourceNodeSerializer */
    private $nodeSerializer;
    /** @var ResourceNodeRepository */
    private $nodeRepo;

    /**
     * ResourceWidgetSerializer constructor.
     *
     * @param TokenStorageInterface  $tokenStorage
     * @param ObjectManager          $om
     * @param ResourceNodeSerializer $nodeSerializer
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        ResourceNodeSerializer $nodeSerializer)
    {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->nodeSerializer = $nodeSerializer;

        $this->nodeRepo = $om->getRepository(ResourceNode::class);
    }

    public function getName()
    {
        return 'resource_widget';
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return ResourceWidget::class;
    }

    public function serialize(ResourceWidget $widget, array $options = []): array
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        $resourceNode = null;

        $dataSource = $widget->getWidgetInstance()->getDataSource();
        if (!empty($dataSource) && 'personal_workspace' === $dataSource->getName() && $user->getPersonalWorkspace()) {
            $resourceNode = $this->nodeRepo->findWorkspaceRoot($user->getPersonalWorkspace());
        } else {
            $resourceNode = $widget->getResourceNode();
        }

        return [
            'resource' => $resourceNode ? $this->nodeSerializer->serialize($resourceNode, [Options::SERIALIZE_MINIMAL]) : null,
            'showResourceHeader' => $widget->getShowResourceHeader(),
        ];
    }

    public function deserialize($data, ResourceWidget $widget, array $options = []): ResourceWidget
    {
        if (isset($data['resource'])) {
            $resourceNode = $this->nodeRepo->findOneBy(['uuid' => $data['resource']['id']]);

            if ($resourceNode) {
                $widget->setResourceNode($resourceNode);
            }
        }
        $this->sipe('showResourceHeader', 'setShowResourceHeader', $data, $widget);

        return $widget;
    }
}
