<?php

namespace Innova\CollecticielBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Innova\CollecticielBundle\Entity\Drop;

/**
 * @DI\Service("innova.manager.drop_manager")
 */
class DropManager
{
    private $container;
    private $em;
    private $dropRepo;

    /**
     * @DI\InjectParams({
     *     "container"  = @DI\Inject("service_container"),
     *     "em"         = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct($container, $em)
    {
        $this->container = $container;
        $this->em = $em;
        $this->dropRepo = $this->em->getRepository('InnovaCollecticielBundle:Drop');
    }

    public function create($dropzone, $user)
    {
        $drop = new Drop();

        $number = ($this->dropRepo->getLastNumber($dropzone) + 1);
        $drop->setNumber($number);
        $drop->setUser($user);
        $drop->setDropzone($dropzone);
        $drop->setFinished(false);

        $this->em->persist($drop);
        $this->em->flush();
        $this->em->refresh($drop);

        return $drop;
    }
}
