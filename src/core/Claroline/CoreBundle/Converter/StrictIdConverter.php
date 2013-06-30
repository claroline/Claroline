<?php

namespace Claroline\CoreBundle\Converter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\EntityManager;

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
     * @{inheritDoc}
     *
     * @throws InvalidConfigurationException if the parameter name, class or id option are missing
     * @throws NotFoundHttpException if the id doesn't matche an existing entity
     */
    public function apply(Request $request, ConfigurationInterface $configuration)
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
     * @{inheritDoc}
     */
    public function supports(ConfigurationInterface $configuration)
    {
        if (!$configuration instanceof ParamConverter) {
            return false;
        }

        $options = $configuration->getOptions();

        if (isset($options['strictId']) && $options['strictId'] === true) {
            return true;
        }

        return false;
    }
}