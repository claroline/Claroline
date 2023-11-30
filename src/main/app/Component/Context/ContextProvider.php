<?php

namespace Claroline\AppBundle\Component\Context;

use Claroline\AppBundle\Component\AbstractComponentProvider;

/**
 * Aggregates all the contexts defined in the Claroline app.
 *
 * A context MUST :
 *   - be declared as a symfony service and tagged with "claroline.component.context".
 *   - implement the ContextInterface interface (or the AbstractContext class).
 *
 * NB. Using the component system for the contexts is a dev convenience.
 * Plugins SHOULD NOT declare new contexts in addition to the ones provided by the Claroline core.
 */
class ContextProvider extends AbstractComponentProvider
{
    public function __construct(
        private readonly iterable $registeredContexts
    ) {
    }

    final public static function getServiceTag(): string
    {
        return 'claroline.component.context';
    }

    /**
     * Get the list of all the contexts injected in the app by the current plugins.
     * It does not contain contexts for disabled plugins.
     */
    protected function getRegisteredComponents(): iterable
    {
        return $this->registeredContexts;
    }

    public function getAvailableContexts(): array
    {
        $available = [];
        foreach ($this->getRegisteredComponents() as $contextComponent) {
            if ($contextComponent->isAvailable() && $contextComponent->isRoot()) {
                $available[] = [
                    'icon' => $contextComponent::getIcon(),
                    'name' => $contextComponent::getName(),
                ];
            }
        }

        return $available;
    }

    public function getContext(string $contextName/* , string $contextId = null */): ContextInterface
    {
        /** @var ContextInterface $contextHandler */
        $contextHandler = $this->getComponent($contextName);
        /*if (!$contextHandler->isAvailable()) {
            throw new \RuntimeException(sprintf('Context "%s(%s)" is not available. Check %s::isAvailable() for more info.', $contextName, $contextId || '', get_class($contextHandler)));
        }*/

        return $contextHandler;
    }
}
