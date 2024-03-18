<?php

namespace Claroline\ThemeBundle\Component\Tool;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Component\Tool\AbstractTool;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Component\Context\AccountContext;
use Claroline\CoreBundle\Component\Context\AdministrationContext;
use Claroline\ThemeBundle\Entity\ColorCollection;
use Claroline\ThemeBundle\Manager\IconSetManager;
use Claroline\ThemeBundle\Manager\ThemeManager;

class AppearanceTool extends AbstractTool
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly ThemeManager $themeManager,
        private readonly IconSetManager $iconSetManager,
        private readonly ParametersSerializer $parametersSerializer,
    ) {
    }

    public static function getName(): string
    {
        return 'appearance';
    }

    public static function getIcon(): string
    {
        return 'paintbrush';
    }

    public function isRequired(string $context, ContextSubjectInterface $contextSubject = null): bool
    {
        return true;
    }

    public function supportsContext(string $context): bool
    {
        return in_array($context, [
            AccountContext::getName(),
            AdministrationContext::getName(),
        ]);
    }

    public function open(string $context, ContextSubjectInterface $contextSubject = null): ?array
    {
        if (AdministrationContext::getName() === $context) {
            $colorCharts = $this->om->getRepository(ColorCollection::class)->findAll();
            $parameters = $this->parametersSerializer->serialize(); // only get appearance options

            return [
                'parameters' => $parameters,
                'availableThemes' => $this->themeManager->getAvailableThemes(),
                'availableIconSets' => $this->iconSetManager->getAvailableSets(),
                'availableColorCharts' => array_map(function (ColorCollection $colorCollection) {
                    return $this->serializer->serialize($colorCollection);
                }, $colorCharts),
            ];
        }
        return [
            'availableThemes' => $this->themeManager->getAvailableThemes(),
            'theme' => $this->themeManager->getAppearance(),
        ];
    }
}
