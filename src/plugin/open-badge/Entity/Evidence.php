<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'claro__open_badge_evidence')]
#[ORM\Entity]
class Evidence
{
    use Id;
    use Uuid;
    use Description;

    #[ORM\Column]
    private ?string $name = null;

    
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Assertion::class, inversedBy: 'evidences')]
    private ?Assertion $assertion = null;

    #[ORM\ManyToOne(targetEntity: ResourceUserEvaluation::class)]
    private ?ResourceUserEvaluation $resourceEvidence = null;

    #[ORM\ManyToOne(targetEntity: Evaluation::class)]
    private ?Evaluation $workspaceEvidence = null;

    
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Rule::class)]
    private ?Rule $rule = null;

    
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $user = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAssertion(): ?Assertion
    {
        return $this->assertion;
    }

    public function setAssertion(Assertion $assertion): void
    {
        $this->assertion = $assertion;
    }

    public function setResourceEvidence(?ResourceUserEvaluation $resourceEvidence): void
    {
        $this->resourceEvidence = $resourceEvidence;
    }

    public function getResourceEvidence(): ResourceUserEvaluation
    {
        return $this->resourceEvidence;
    }

    public function setWorkspaceEvidence(?Evaluation $workspaceEvidence): void
    {
        $this->workspaceEvidence = $workspaceEvidence;
    }

    public function getWorkspaceEvidence(): Evaluation
    {
        return $this->workspaceEvidence;
    }

    public function setRule(Rule $rule): void
    {
        $this->rule = $rule;
    }

    public function getRule(): ?Rule
    {
        return $this->rule;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
