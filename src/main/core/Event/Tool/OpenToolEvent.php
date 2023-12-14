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

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\CoreBundle\Entity\User;
use Symfony\Contracts\Translation\TranslatorInterface;

class OpenToolEvent extends AbstractToolEvent
{
    private ?User $user;
    private array $data = [];

    public function __construct(
        string $toolName,
        string $context,
        ContextSubjectInterface $contextSubject = null,
        User $user = null
    ) {
        parent::__construct($toolName, $context, $contextSubject);

        $this->user = $user;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Sets response data to return in the api.
     * NB. It MUST contain serialized structures.
     */
    public function addResponse(array $responseData): void
    {
        $this->data = array_merge($responseData, $this->data);
    }

    public function getResponse(): array
    {
        return $this->data;
    }

    /**
     * Sets data to return in the api.
     * NB. It MUST contain serialized structures.
     *
     * @deprecated use addResponse(array $responseData)
     */
    public function setData(array $data): void
    {
        $this->addResponse($data);
    }

    /**
     * @deprecated use getResponse()
     */
    public function getData(): array
    {
        return $this->getResponse();
    }

    /**
     * @deprecated nope
     */
    public function getMessage(TranslatorInterface $translator): string
    {
        return $translator->trans('toolOpen', ['userName' => $this->user->getUsername(), 'context' => $this->getContext(), 'toolName' => $this->getToolName()], 'tools');
    }
}
