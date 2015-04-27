<?php

namespace HeVinci\CompetencyBundle\DataFixtures\Batch;

use Claroline\CoreBundle\Entity\User;
use HeVinci\CompetencyBundle\Util\DataFixture;

class UserFixture extends DataFixture
{
    public function load()
    {
        $manager = $this->container->get('claroline.manager.user_manager');
        $this->flushSuites($this->loadUserData(), 5, function ($user) use ($manager) {
            $user = $this->buildUser($user[0], $user[1]);
            $manager->createUser($user, false);
        });
    }

    public function unload()
    {
        $userNames = array_map(function ($user) {
            return  strtolower($user[0] . $user[1]);
        }, $this->loadUserData());

        $this->createQueryBuilder()
            ->delete('Claroline\CoreBundle\Entity\User', 'u')
            ->where('u.username IN (:userNames)')
            ->getQuery()
            ->setParameter(':userNames', $userNames)
            ->execute();

        $this->createQueryBuilder()
            ->delete('Claroline\CoreBundle\Entity\Workspace\Workspace', 'w')
            ->where('w.code IN (:userNames)')
            ->getQuery()
            ->setParameter(':userNames', $userNames)
            ->execute();
    }

    private function loadUserData()
    {
        return $this->loadCsvData(__DIR__ . '/../files/users.csv', ' ');
    }

    private function buildUser($firstName, $lastName)
    {
        $user = new User();
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setUsername($username = strtolower($firstName . $lastName));
        $user->setPassword($username);
        $user->setMail("{$username}@mail.com");

        return $user;
    }
} 