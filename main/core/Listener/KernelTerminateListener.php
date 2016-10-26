<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

/**
 * @DI\Service()
 */
class KernelTerminateListener
{
    private $elementsToRemove;

    public function __construct()
    {
        $this->elementsToRemove = [];
    }

    public function addElementToRemove($element)
    {
        $this->elementsToRemove[] = $element;
    }

    /**
     * @DI\Observe("kernel.terminate")
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        if (count($this->elementsToRemove) > 0) {
            $fs = new FileSystem();

            foreach ($this->elementsToRemove as $element) {
                $fs->remove($element);
            }
        }
    }
}
