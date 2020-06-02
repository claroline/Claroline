<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Resource\Types;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Manager\Template\PlaceholderManager;

class TextListener
{
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var PlaceholderManager */
    private $placeholderManager;

    /**
     * TextListener constructor.
     *
     * @param ObjectManager      $om
     * @param SerializerProvider $serializer
     * @param PlaceholderManager $placeholderManager
     */
    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        PlaceholderManager $placeholderManager)
    {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->placeholderManager = $placeholderManager;
    }

    /**
     * Loads a Text resource.
     *
     * @param LoadResourceEvent $event
     */
    public function load(LoadResourceEvent $event)
    {
        $event->setData([
            'text' => $this->serializer->serialize($event->getResource()),
            'placeholders' => $this->placeholderManager->getAvailablePlaceholders(),
        ]);

        $event->stopPropagation();
    }

    /**
     * @param DeleteResourceEvent $event
     */
    public function delete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }
}
