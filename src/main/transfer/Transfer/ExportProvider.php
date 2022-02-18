<?php

namespace Claroline\TransferBundle\Transfer;

use Claroline\TransferBundle\Transfer\Exporter\ExporterInterface;

class ExportProvider extends AbstractProvider
{
    public function execute(string $format, string $action, ?array $options = [], ?array $extra = [])
    {
        $executor = $this->getAction($action);
        if (!$executor->supports($format, $options, $extra)) {
            return [];
        }

        $data = $executor->execute($options, $extra);

        $adapter = $this->getAdapter($format);

        return $adapter->format($data, $options);
    }

    /**
     * Returns a list of available importers for a given format (mime type).
     */
    public function getAvailableActions(string $format, ?array $options = [], ?array $extra = []): array
    {
        $supportedActions = array_filter(iterator_to_array($this->actions), function (ExporterInterface $action) use ($format, $options, $extra) {
            return $action->supports($format, $options, $extra);
        });

        $available = [];
        foreach ($supportedActions as $action) {
            $schema = $action->getAction();
            $available[$schema[0]][$schema[1]] = $action->getExtraDefinition($options, $extra);
        }

        return $available;
    }
}
