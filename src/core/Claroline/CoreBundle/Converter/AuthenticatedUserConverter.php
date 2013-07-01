<?php

namespace Claroline\CoreBundle\Converter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\User;

/**
 * @DI\Service()
 * @DI\Tag("request.param_converter", attributes={"priority" = 500})
 *
 * Adds the current authenticated user in the request attributes.
 */
class AuthenticatedUserConverter implements ParamConverterInterface
{
    private $securityContext;

    /**
     * @DI\InjectParams({
     *     "securityContext" = @DI\Inject("security.context")
     * })
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * @{inheritDoc}
     *
     * @throws InvalidConfigurationException if the parameter name is missing
     * @throws AccessDeniedHttpException if the current user is not authenticated
     */
    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        if (null === $parameter = $configuration->getName()) {
            throw new InvalidConfigurationException(InvalidConfigurationException::MISSING_NAME);
        }

        if (($user = $this->securityContext->getToken()->getUser()) instanceof User) {
            $request->attributes->set($parameter, $user);

            return true;
        }

        throw new AccessDeniedHttpException();
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

        if (isset($options['authenticatedUser']) && $options['authenticatedUser'] === true) {
            return true;
        }

        return false;
    }
}