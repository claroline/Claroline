<?php

namespace Claroline\AppBundle\API\Finder\Type;

use Claroline\AppBundle\API\Finder\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Apply the Public filter to a Finder.
 * It will ensure Anonymous users only get accesses to entities marked as public {@see IsPublic}.
 */
class PublicType extends AbstractType
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'default' => !$this->authorization->isGranted('IS_AUTHENTICATED_FULLY') ?: null,
        ]);

        $resolver->setAllowedValues('default', [null, true, false]);
    }

    public function submit(mixed $filterValue, array $options): ?bool
    {
        $value = $filterValue;
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            // force public elements for anonymous
            $value = true;
        } else {
            $requestValue = null;
            if (null !== $value) {
                $requestValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }

            $value = null === $requestValue ? $options['default'] : $requestValue;
        }

        return $value;
    }

    public function getParent(): ?string
    {
        return BooleanType::class;
    }
}
