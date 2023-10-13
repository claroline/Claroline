<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Tool;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Contracts\Translation\TranslatorInterface;

class OpenToolEvent extends AbstractToolEvent
{
    private ?User $user;
    private array $data = [];

    public function __construct(
        string $toolName,
        string $context,
        ?Workspace $workspace = null,
        ?User $user = null
    ) {
        parent::__construct($toolName, $context, $workspace);

        $this->user = $user;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Sets data to return in the api.
     * NB. It MUST contain serialized structures.
     */
    public function setData(array $data): void
    {
        $this->data = array_merge($data, $this->data);
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @deprecated nope
     */
    public function getMessage(TranslatorInterface $translator): string
    {
        return $translator->trans('toolOpen', ['userName' => $this->user->getUsername(), 'context' => $this->getContext(), 'toolName' => $this->getToolName()], 'tools');
    }
}
