<?php

namespace Claroline\TransferBundle\Transfer;

use Claroline\TransferBundle\Transfer\Adapter\AdapterInterface;

abstract class AbstractProvider
{
    /** @var iterable|AdapterInterface[] */
    protected $adapters = [];

    /** @var iterable|ActionInterface[] */
    protected $actions = [];

    abstract public function getAvailableActions(string $format, ?array $options = [], ?array $extra = []): array;

    public function setAdapters(iterable $adapters)
    {
        $this->adapters = $adapters;
    }

    /**
     * Returns an adapter for a given mime type.
     */
    protected function getAdapter(string $mimeType): AdapterInterface
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter->supports($mimeType)) {
                return $adapter;
            }
        }

        throw new \Exception('No adapter found for mime type '.$mimeType);
    }

    public function setActions(iterable $actions)
    {
        $this->actions = $actions;
    }

    public function getAction(string $actionName): ActionInterface
    {
        foreach ($this->actions as $action) {
            if ($actionName === $this->getActionName($action)) {
                return $action;
            }
        }

        throw new \Exception('No action found for name '.$actionName);
    }

    protected function getActionName(ActionInterface $action): string
    {
        return $action::getAction()[0].'_'.$action::getAction()[1];
    }
}
