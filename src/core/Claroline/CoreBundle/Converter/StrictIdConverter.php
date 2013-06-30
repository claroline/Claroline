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

    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        if (null === $parameter = $configuration->getName()) {
            throw new InvalidConfigurationException('the controller parameter name is mandatory');
        }

        if (null === $entityClass = $configuration->getClass()) {
            throw new InvalidConfigurationException('the "class" field is mandatory');
        }

        $options = $configuration->getOptions();

        if (!isset($options['id'])) {
            throw new InvalidConfigurationException('the "id" option is mandatory');
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