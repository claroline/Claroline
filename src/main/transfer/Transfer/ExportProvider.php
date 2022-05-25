<?php

namespace Claroline\TransferBundle\Transfer;

use Claroline\TransferBundle\Transfer\Exporter\ExporterInterface;

class ExportProvider extends AbstractProvider
{
    public function execute(string $fileDest, string $format, string $action, ?array $options = [], ?array $extra = [])
    {
        $adapter = $this->getAdapter($format);
        $executor = $this->getAction($action);
        if (!$executor->supports($format, $options, $extra)) {
            return;
        }

        $i = 0;
        do {
            $data = $executor->execute($i, $options, $extra);
            $adapter->dump($fileDest, $data, $options, $extra, 0 !== $i);

            ++$i;
        } while (!empty($data));
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
            $actionDef = $action->getAction();

            $available[$actionDef[0]][$actionDef[1]] = array_merge(
                $action->getSchema($options, $extra),
                $action->getExtraDefinition($options, $extra)
            );
        }

        return $available;
    }
}
