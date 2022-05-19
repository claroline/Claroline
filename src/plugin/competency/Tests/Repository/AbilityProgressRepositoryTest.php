<?php

namespace HeVinci\CompetencyBundle\Repository;

use HeVinci\CompetencyBundle\Entity\Progress\AbilityProgress;
use HeVinci\CompetencyBundle\Util\RepositoryTestCase;

class AbilityProgressRepositoryTest extends RepositoryTestCase
{
    public function testFindByAbilitiesAndStatus()
    {
        $repo = $this->om->getRepository(AbilityProgress::class);

        $user = $this->persistUser('john');
        $a1 = $this->persistAbility('a1');
        $a2 = $this->persistAbility('a2');
        $a3 = $this->persistAbility('a3');
        $a4 = $this->persistAbility('a4');
        $this->persistAbilityProgress($user, $a1, AbilityProgress::STATUS_ACQUIRED);
        $this->persistAbilityProgress($user, $a2, AbilityProgress::STATUS_ACQUIRED);
        $this->persistAbilityProgress($user, $a3, AbilityProgress::STATUS_ACQUIRED);
        $this->persistAbilityProgress($user, $a4, AbilityProgress::STATUS_NOT_ATTEMPTED);
        $this->om->flush();

        $progresses = $repo->findByAbilitiesAndStatus($user, [$a2, $a3, $a4], AbilityProgress::STATUS_ACQUIRED);

        $this->assertEquals(2, count($progresses));
        $this->assertEquals($a2, $progresses[0]->getAbility());
        $this->assertEquals($a3, $progresses[1]->getAbility());
    }
}
