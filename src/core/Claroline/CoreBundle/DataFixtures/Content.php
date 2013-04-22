<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Claroline\CoreBundle\Entity\Home\Content;
use Claroline\CoreBundle\Entity\Home\Type;
use Claroline\CoreBundle\Entity\Home\Content2Type;

class Contents extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $titles = array(
            'ClarolineConnect© : plateforme Claroline de nouvelle génération.',
            'ClarolineConnect© Demo'
        );

        $texts = array(
            "Cet espace de démonstration propose un aperçu des diverses fonctionnalités de ce LMS
            (Learning Management System) résolument tourné vers les usages d'aujourd'hui et les technologies
            de demain, tout en respectant les objectifs fondamentaux du projet Claroline: simplicité d'utilisation,
            souplesse de mise en oeuvre et stabilité du code.

            La volonté des auteurs est aussi de permettre l'utilisation du logiciel par le plus grand nombre,
            d'où le choix d'une licence Open Source pour sa diffusion.

            Conçue pour satisfaire le monde de l’enseignement, de la formation mais aussi de l'entreprise,
            la plateforme ClarolineConnect© (dont la version Bêta sortira en septembre 2013),
            permet aux utilisateurs une plus grande ouverture vers le web et les outils collaboratifs.

            Davantage centrée sur l'utilisateur, ClarolineConnect© propose des outils d’apprentissage
            performants en intégrant des fonctions de type réseau social ainsi que des outils communautaires
            et interactifs (wiki, forum, blog ...).

            Libre d'élaborer son environnement personnel d'apprentissage ou de construire un dispositif
            pédagogique au moyen d'outils variés et adaptés au contexte, dans un environnement
            ultra-personnalisable, l'utilisateur pourra partager, gérer, stocker, diffuser l'information
            tout en disposant d'un haut niveau de suivi des activités.

            Interconnectée avec son environnement, la nouvelle plateforme ClarolineConnect©, actualisée sur
            le plan ergonomique, permettra à l'ensemble des utilisateurs de travailler davantage ensemble,
            avec un accès à plus de technologies et de fonctionnalités, tout en conservant la simplicité d'usage,
            la souplesse de mise en oeuvre et la stabilité du code.",

            "Administrateur:

            Nom d'utilisateur: JohnDoe
            Mot de passe: JohnDoe

            Professeur:

            Nom d'utilisateur: JaneDoe
            Mot de passe: JaneDoe"
        );

        $sizes = array("span8", "span4");

        $type = $manager->getRepository("ClarolineCoreBundle:Home\Type")->findOneBy(array('name' => 'home'));

        if ($type) {

            foreach ($titles as $i => $title) {

                $content[$i] = new Content();
                $content[$i]->setTitle($title);
                $content[$i]->setContent($texts[$i]);

                $first = $manager->getRepository("ClarolineCoreBundle:Home\Content2Type")->findOneBy(
                    array('back' => null, 'type' => $type)
                );

                $contentType = new Content2Type($first);

                $contentType->setContent($content[$i]);
                $contentType->setType($type);
                $contentType->setSize($sizes[$i]);

                $manager->persist($contentType);

                $manager->persist($content[$i]);

                $manager->flush();

            }
        }
    }

    public function getOrder()
    {
        return 8; // the order in which fixtures will be loaded
    }
}
