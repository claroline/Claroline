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

use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service()
 * @DI\Tag("request.param_converter", attributes={"priority" = 500})
 *
 * Adds the current authenticated user in the request attributes.
 */
class AuthenticatedUserConverter implements ParamConverterInterface
{
    private $tokenStorage;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "translator"   = @DI\Inject("translator")
     * })
     */
    public function __construct(TokenStorageInterface $tokenStorage, TranslatorInterface $translator)
    {
        $this->tokenStorage = $tokenStorage;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigurationException if the parameter name is missing
     * @throws AccessDeniedException         if the current request is anonymous
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        if (null === $parameter = $configuration->getName()) {
            throw new InvalidConfigurationException(InvalidConfigurationException::MISSING_NAME);
        }

        $options = $configuration->getOptions();

        if ($options['authenticatedUser'] === true) {
            if (($user = $this->tokenStorage->getToken()->getUser()) instanceof User) {
                $request->attributes->set($parameter, $user);

                return true;
            } else {
                if (array_key_exists('messageEnabled', $options) && $options['messageEnabled'] === true) {
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
                        $this->translator->trans($messageTranslationKey, [], $messageTranslationDomain)
                    );
                }

                throw new AccessDeniedException();
            }
        } else {
            $request->attributes->set($parameter, $user = $this->tokenStorage->getToken()->getUser());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        $options = $configuration->getOptions();

        if (isset($options['authenticatedUser']) && is_bool($options['authenticatedUser'])) {
            return true;
        }

        return false;
    }
}
