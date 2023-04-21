<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\AppBundle\Entity\FromPlugin;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * DataSource entity.
 *
 * Describes a DataSource provided by a plugin.
 *
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\DataSourceRepository")
 * @ORM\Table(name="claro_data_source")
 */
class DataSource
{
    use Id;
    use Uuid;
    use FromPlugin;

    const CONTEXT_DESKTOP = 'desktop';
    const CONTEXT_WORKSPACE = 'workspace';
    const CONTEXT_ADMINISTRATION = 'administration';
    const CONTEXT_HOME = 'home';

    /**
     * The name of the source.
     *
     * @var string
     *
     * @ORM\Column(name="source_name")
     */
    private $name;

    /**
     * The type of the source.
     *
     * @var string
     *
     * @ORM\Column(name="source_type")
     */
    private $type;

    /**
     * The context of the source (workspace, desktop).
     *
     * @ORM\Column(type="json")
     *
     * @var array
     */
    private $context = [
        self::CONTEXT_DESKTOP,
        self::CONTEXT_WORKSPACE,
        self::CONTEXT_ADMINISTRATION,
        self::CONTEXT_HOME,
    ];

    /**
     * A list of tags to group similar sources.
     *
     * @ORM\Column(type="json")
     *
     * @var array
     */
    private $tags = [];

    /**
     * DataSource constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type.
     *
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get the context of the source (workspace, desktop).
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set context.
     */
    public function setContext(array $context)
    {
        $this->context = $context;
    }

    /**
     * Get tags.
     *
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set tags.
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;
    }
}
