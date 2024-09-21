<?php

namespace UJM\ExoBundle\Entity\ItemType;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * An Audio Content item.
 */
#[ORM\Table(name: 'ujm_item_content')]
#[ORM\Entity]
class ContentItem extends AbstractItem
{
    #[ORM\Column(name: 'content_data', type: Types::TEXT)]
    private $data;

    /**
     * Gets data.
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets data.
     *
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    public function isContentItem()
    {
        return true;
    }
}
