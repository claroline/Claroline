<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A long description of an entity.
 * It can contain any text length and HTML markup.
 *
 * NB. often used with Meta\Description (used to display a short description in lists/cards)
 */
trait DescriptionHtml
{
    #[ORM\Column(name: 'description_html', type: Types::TEXT, nullable: true)]
    protected ?string $descriptionHtml = null;

    public function getDescriptionHtml(): ?string
    {
        return $this->descriptionHtml;
    }

    public function setDescriptionHtml(string $description = null): void
    {
        $this->descriptionHtml = $description;
    }
}
