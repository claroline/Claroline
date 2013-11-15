<?php

namespace Claroline\CoreBundle\Converter;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
    private $translator;

    /**
     * @DI\InjectParams({
     *     "securityContext" = @DI\Inject("security.context"),
     *     "translator" = @DI\Inject("translator")
     * })
     */
    public function __construct(SecurityContextInterface $securityContext, Translator $translator)
    {
        $this->securityContext  = $securityContext;
        $this->translator       = $translator;
    }

    /**
     * @{inheritDoc}
     *
     * @throws InvalidConfigurationException if the parameter name is missing
     * @throws AccessDeniedHttpException     if the current user is not authenticated
     */
    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        if (null === $parameter = $configuration->getName()) {
            throw new InvalidConfigurationException(InvalidConfigurationException::MISSING_NAME);
        }

        $options = $configuration->getOptions();
        if ($options['authenticatedUser'] === true) {
            if (($user = $this->securityContext->getToken()->getUser()) instanceof User) {
                $request->attributes->set($parameter, $user);

                return true;
            } else {
                if (array_key_exists('messageEnabled', $options) and $options['messageEnabled'] === true) {
                    $messageType = 'warning';
                    if (array_key_exists('messageType', $options)) {
                        $messageType = $options['messageType'];
                    }

                    $messageTranslationKey = 'this_page_requires_authentication';
                    $messageTranslationDomain = 'platform';
                    if (array_key_exists('messageTranslationKey', $options)) {
                        $messageTranslationKey = $options['messageTranslationKey'];
                        if (array_key_exists('messageTranslationDomain', $options)) {
                            $messageTranslationDomain = $options['messageTranslationDomain'];
                        }
                    }

                    $request->getSession()->getFlashBag()->add(
                        $messageType,
                        $this->translator->trans($messageTranslationKey, array(), $messageTranslationDomain)
                    );
                }

                throw new AccessDeniedException();
            }
        } else {
            $request->attributes->set($parameter, $user = $this->securityContext->getToken()->getUser());
        }
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

        if (isset($options['authenticatedUser']) && ($options['authenticatedUser'] === true || $options['authenticatedUser'] === false )) {
            return true;
        }

        return false;
    }
}
