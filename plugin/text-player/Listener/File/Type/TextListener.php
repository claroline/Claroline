<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TextPlayerBundle\Listener\File\Type;

use Claroline\CoreBundle\Event\Resource\File\LoadFileEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Integrates Text files into Claroline.
 *
 * @DI\Service()
 */
class TextListener
{
    /**
     * @DI\Observe("file.text.load")
     *
     * @param LoadFileEvent $event
     */
    public function onLoad(LoadFileEvent $event)
    {
        $event->setData([
            'isHtml' => 'text/html' === $event->getResource()->getMimeType(),
            'content' => utf8_encode(file_get_contents($event->getPath())),
        ]);

        $event->stopPropagation();
    }
}
