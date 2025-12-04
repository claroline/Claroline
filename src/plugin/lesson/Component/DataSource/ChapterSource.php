<?php

namespace Icap\LessonBundle\Component\DataSource;

use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\DataSource\ListSourceComponent;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Security\PlatformRoles;
use Icap\LessonBundle\Finder\ChapterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ChapterSource extends ListSourceComponent
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getName(): string
    {
        return 'lesson_chapters';
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            DesktopContext::getName(),
            WorkspaceContext::getName(),
        ]);
    }

    public static function getClass(): string
    {
        return ChapterType::class;
    }

    protected function getRequest(string $context, ?ContextSubjectInterface $contextSubject = null, ?bool $filterSubject = true, ?Request $request = null): FinderRequest
    {
        $finderRequest = parent::getRequest($context, $contextSubject, $filterSubject, $request);

        $roles = $this->tokenStorage->getToken()?->getRoleNames() ?? [PlatformRoles::ANONYMOUS];
        if (!in_array(PlatformRoles::ADMIN, $roles)) {
            $finderRequest->addFilter('resourceNode.roles', $roles);
        }

        return $finderRequest;
    }
}
