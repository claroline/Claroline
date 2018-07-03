<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\AppBundle\Entity\FromPlugin;
use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;

/**
 * DataSource entity.
 *
 * Describes a DataSource provided by a plugin.
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_data_source")
 */
class DataSource
{
    use Id;
    use FromPlugin;

    const CONTEXT_DESKTOP = 'desktop';
    const CONTEXT_WORKSPACE = 'workspace';

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
     * @ORM\Column(type="json_array")
     *
     * @var array
     */
    private $context = [
        self::CONTEXT_DESKTOP,
        self::CONTEXT_WORKSPACE,
    ];

    /**
     * A list of tags to group similar sources.
     *
     * @ORM\Column(type="json_array")
     *
     * @var array
     */
    private $tags = [];

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
     *
     * @param array $context
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
     *
     * @param array $tags
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;
    }
}
