<?php

namespace Claroline\CoreBundle\Library\Resource;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Library\Security\Utilities as SecurityUtilities;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.resource.converter")
 */
class Converter
{
    /* @var EntityManager */
    private $em;
    private $ut;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "ut" = @DI\Inject("claroline.security.utilities"),
     *     "translator" = @DI\Inject("translator")
     * })
     */
    public function __construct(EntityManager $em, SecurityUtilities $ut, $translator)
    {
        $this->em = $em;
        $this->ut = $ut;
        $this->translator = $translator;
    }

    /**
     * Convert a ressource into an json string (mainly used to be sent to the manager.js)
     *
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     *
     * @return array
     *
     * @todo this method shouldn't wrap the converted resource in an additional array
     */
    public function toJson(AbstractResource $resource, TokenInterface $token)
    {
        $phpArray[0] = $this->toArray($resource, $token);
        $json = json_encode($phpArray);

        return $json;
    }
}