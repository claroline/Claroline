<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Converter;

use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @DI\Service()
 * @DI\Tag("request.param_converter", attributes={"priority" = 500})
 *
 * Retreives an entity by its id (no further guessing) and adds it to the request
 * attributes. The matching between the entity id and the request id attribute
 * must be explicit.
 */
class StrictIdConverter implements ParamConverterInterface
{
    private $em;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigurationException if the parameter name, class or id option are missing
     * @throws NotFoundHttpException         if the id doesn't matche an existing entity
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        if (null === $parameter = $configuration->getName()) {
            throw new InvalidConfigurationException(InvalidConfigurationException::MISSING_NAME);
        }

        if (null === $entityClass = $configuration->getClass()) {
            throw new InvalidConfigurationException(InvalidConfigurationException::MISSING_CLASS);
        }

        $options = $configuration->getOptions();

        if (!isset($options['id'])) {
            throw new InvalidConfigurationException(InvalidConfigurationException::MISSING_ID);
        }

        if ($request->attributes->has($options['id'])) {
            if (null !== $id = $request->attributes->get($options['id'])) {
                if (null !== $entity = $this->em->getRepository($entityClass)->find($id)) {
                    $request->attributes->set($parameter, $entity);

                    return true;
                }
            }

            if (!$configuration->isOptional()) {
                throw new NotFoundHttpException();
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        $options = $configuration->getOptions();

        if (isset($options['strictId']) && $options['strictId'] === true) {
            return true;
        }

        return false;
    }
}
