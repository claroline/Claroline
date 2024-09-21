<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_text_revision')]
#[ORM\Entity]
class Revision
{
    use Id;

    #[ORM\Column(type: Types::INTEGER)]
    private int $version = 1;

    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Text::class, inversedBy: 'revisions', cascade: ['persist'])]
    private ?Text $text;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'])]
    private ?User $user;

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
    }

    public function setText(Text $text): void
    {
        $this->text = $text;
        $text->addRevision($this);
    }

    public function getText(): ?Text
    {
        return $this->text;
    }

    public function setContent($content): void
    {
        $this->content = $content;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
