<?php

namespace Claroline\CoreBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Claroline\CoreBundle\Repository\DataSourceRepository;
use Claroline\AppBundle\Entity\FromPlugin;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * DataSource entity.
 *
 * Describes a DataSource provided by a plugin.
 *
 *
 */
#[ORM\Table(name: 'claro_data_source')]
#[ORM\Entity(repositoryClass: DataSourceRepository::class)]
class DataSource
{
    use Id;
    use Uuid;
    use FromPlugin;

    /** @deprecated use Claroline\CoreBundle\Component\Context\DesktopContext::getName() */
    public const CONTEXT_DESKTOP = 'desktop';
    /** @deprecated use Claroline\CoreBundle\Component\Context\WorkspaceContext::getName() */
    public const CONTEXT_WORKSPACE = 'workspace';
    /** @deprecated use Claroline\CoreBundle\Component\Context\AdministrationContext::getName() */
    public const CONTEXT_ADMINISTRATION = 'administration';
    /** @deprecated use Claroline\CoreBundle\Component\Context\PublicContext::getName() */
    public const CONTEXT_HOME = 'public';

    #[ORM\Column(name: 'source_name')]
    private ?string $name = null;

    #[ORM\Column(name: 'source_type')]
    private ?string $type = null;

    #[ORM\Column(type: Types::JSON)]
    private ?array $context = [
        self::CONTEXT_DESKTOP,
        self::CONTEXT_WORKSPACE,
        self::CONTEXT_ADMINISTRATION,
        self::CONTEXT_HOME,
    ];

    /**
     * A list of tags to group similar sources.
     */
    #[ORM\Column(type: Types::JSON)]
    private ?array $tags = [];

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }
}
