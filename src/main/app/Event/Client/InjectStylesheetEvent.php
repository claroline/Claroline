<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Event\Client;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * An event dispatched when the application UI is rendered
 * giving the chance to plugins to inject some custom styles on any application page.
 */
class InjectStylesheetEvent extends Event
{
    private string $content = '';

    public function addContent(string $content): void
    {
        $this->content .= $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
