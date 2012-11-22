<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Claroline\CoreBundle\Entity\Group;

class LoadGroupData extends AbstractFixture implements OrderedFixtureInterface
{
    protected $groupsName;

    public function __construct($groupsName = null)
    {
        if ($groupsName !== null) {
            $this->groupsName = $groupsName;
        } else {
            $this->groupsName = array('group_a', 'group_b', 'group_c');
        }
    }

    /**
     * Loads three groups with the following roles :
     *
     * Group A : ROLE_A
     * Group B : ROLE_D (i.e. ROLE_C -> ROLE_D)
     * Group C : ROLE_F (i.e. ROLE_C -> ROLE_E -> ROLE_F)
     */
    public function load(ObjectManager $manager)
    {
        $groups = array(
            'group_a' => array('Group A', array('user', 'user_2'), 'role_a'),
            'group_b' => array('Group B', array('user_3'), 'role_d'),
            'group_c' => array('Group C', null, 'role_f')
        );

        foreach($this->groupsName as $groupName){

            if (array_key_exists($groupName, $groups)) {

                $group = new Group();
                $group->setName($groups[$groupName][0]);
                $group->addRole($this->getReference('role/'.$groups[$groupName][2]));
                $usernames = $groups[$groupName][1];

                if (null !== $usernames){
                    foreach($usernames as $username){
                        try{
                            $user = $this->getReference('user/'.$username);
                        $group->addUser($user);
                        } catch(\Exception $e){
                            //nothing
                        }
                    }
                }

                $manager->persist($group);
                $this->addReference('group/'.$groupName, $group);
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 4;
    }
}