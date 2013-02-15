<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Fixtures\LoggableFixture;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\PlatformRoles;

class LoadUsersData extends LoggableFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface $container */
    private $container;

    private $nbUsers;
    private $role;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function __construct($nbUsers, $role = null)
    {
        $this->role = $role;
        $this->nbUsers = $nbUsers;

        $this->firstNames = array(
            "Mary", "Amanda", "James", "Patricia", "Michael", "Sarah", "Patrick", "Homer", "Bart", "Marge", "Lisa",
            "John", "Stan", "Stéphane", "Emmanuel", "Nicolas", "Frédéric", "Luke", "Luc", "Kenneth", "Stanley",
            "Kyle", "Léopold", "Eric", "Cécile", "Marie", "Caterine", "Jessica", "Matthieu", "Aurélie", "Elisabeth",
            "Louis", "Jérome", "Ned", "Ralph", "Charles-Montgomery",
            "Waylon", "Carl", "Timothy", "Kirk", "Milhouse", "Todd", "Maude", "Benjamen", "ObiWan", "George",
            "Barack","Alfred", "Paul", "Gabriel", "Anne", "Théophile", "Bill", "Claudia", "Silva", "Ford",
            "Rodney", "Greg", "Bob", "Robert","Jean-Kévin", "Charles-Henry", "Douglas", "Arthur", "Marvin",
            "Bruce", "William", "Jason", "Mélanie", "Sophie","Dominique", "Coralie", "Camille", "Claudia",
            "Margareth", "Antonio", "Scarlett", "Marie", "Robert", "Hélène", "14M4M3G4Z0RD","Frank",
            "Mélissa", "Elio", "Fabienne", "Thomas", "Jean-Kevin", "Emilie", "Marion", "Perinne", "Corinne",
            "Chloé"
        );

        $this->lastNames = array(
            "Johnson", "Miller", "Brown", "Williams", "Davis", "Simpson", "Smith", "Doe", "Klein", "Godfraind",
            "Gervy", "Fervaille","Minne", "Skywalker", "Marsh", "Broflovski", "Cartman", "Stotch", "McCormick",
            "McLane", "Bourne", "Yates", "Marilyn","McElroy", "Flanders", "Wiggum", "Burns", "Smithers",
            "Carlson", "LoveJoy", "Van Houten", "Gates", "Braconier", "Kenobi","Lucas", "Clooney", "Harisson",
            "Obama", "Bush", "Black", "Hogan", "Anderson", "McKay", "Fields", "Bruel", "Kottick","Dupond",
            "Leloux", "Miller", "Adams", "Dent", "Accroc", "Prefect", "Escort", "Sheridan", "William", "Willis",
            "Lee","Devos", "Tatcher", "Gilbert", "Casilli", "Wilson", "Cantor", "Descartes", "Carlyle", "Ford",
            "Tortelloni", "Pizza"
        );

        $this->maxFirstNameOffset = count($this->firstNames);
        $this->maxFirstNameOffset--;
        $this->maxLastNameOffset = count($this->lastNames);
        $this->maxLastNameOffset--;
    }

    public function load(ObjectManager $manager)
    {
        $role = $this->loadRole($this->role);

        for ($i = 0; $i < $this->nbUsers; $i++) {
            $user = new User();
            $user->setFirstName($this->firstNames[mt_rand(0, $this->maxFirstNameOffset)]);
            $user->setLastName($this->lastNames[mt_rand(0, $this->maxLastNameOffset)]);
            $user->setUsername($user->getFirstName() . $user->getLastName() . rand(0, 1000));
            $user->setMail($user->getUsername() . '@ucl.be');
            $user->setAdministrativeCode('UCL-' . $user->getUsername());
            $user->setPlainPassword('123');
            $user->addRole($role);
            $user = $this->container->get('claroline.user.creator')->create($user);
            $this->log(" {$i} users created");
        }

        $manager->flush();
        $this->log("{$i} users created");
        $this->log("Done");
    }

    protected function loadRole($role)
    {
        $roleRepo = $this->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Role');

        if ($role == 'admin') {
            return $roleRepo->findOneByName(PlatformRoles::ADMIN);
        } elseif ($role == 'ws_creator') {
            return $roleRepo->findOneByName(PlatformRoles::WS_CREATOR);
        } else {
            return $roleRepo->findOneByName(PlatformRoles::USER);
        }
    }
}
