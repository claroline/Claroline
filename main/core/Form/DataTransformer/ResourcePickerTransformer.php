<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\DataTransformer;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @Service("claroline.transformer.resource_picker")
 */
class ResourcePickerTransformer implements DataTransformerInterface
{
    private $resourceManager;

    /**
     * @InjectParams({
     *     "persistence"        = @Inject("claroline.persistence.object_manager"),
     *     "resourceManager"    = @Inject("claroline.manager.resource_manager")
     * })
     */
    public function __construct(ResourceManager $resourceManager)
    {
        $this->resourceManager = $resourceManager;
    }

    public function transform($resourceNode)
    {
        if ($resourceNode instanceof ResourceNode) {
            return $resourceNode->getId();
        }

        return '';
    }

    public function reverseTransform($id)
    {
        if (!$id) {
            return;
        }

        $resourceNode = $this->resourceManager->getById($id);

        if (null === $resourceNode) {
            throw new TransformationFailedException();
        }

        return $resourceNode;
    }
}
