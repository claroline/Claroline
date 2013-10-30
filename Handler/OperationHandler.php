<?php

namespace Claroline\BundleRecorder\Handler;

use Claroline\BundleRecorder\Operation;

class OperationHandler extends BaseHandler
{
    private $document;
    private $rootElement;
    private $isPreviousFileChecked = false;

    public function __construct($operationFile, \Closure $logger = null)
    {
        parent::__construct($operationFile, $logger);
        $this->document = new \DOMDocument('1.0', 'UTF-8');
        $this->document->formatOutput = true;
        $this->rootElement = $this->document->createElement('operations');
        $this->document->appendChild($this->rootElement);
    }

    public function addOperation(Operation $operation)
    {
        $this->log("Logging {$operation->getType()} action in the operation file...");
        $opNode = $this->document->createElement($operation->getType());
        $opNode->nodeValue = $operation->getBundleFqcn();
        $opNode->setAttribute('type', $operation->getBundleType());

        if ($operation->getType() === Operation::UPDATE) {
            $opNode->setAttribute('from', $operation->getFromVersion());
            $opNode->setAttribute('to', $operation->getToVersion());
        }

        $this->rootElement->appendChild($opNode);
        $this->writeOperations();
    }

    public function getOperations()
    {
        if ('' === trim(file_get_contents($this->targetFile))) {
            return array();
        }

        $document = new \DOMDocument();
        $document->load($this->targetFile);
        $operations = array();

        foreach ($document->documentElement->childNodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                $operationType = $node->nodeName;
                $bundleFqcn = $node->nodeValue;
                $bundleType = $node->getAttribute('type');
                $operation = new Operation($operationType, $bundleFqcn, $bundleType);

                if ($operationType === Operation::UPDATE) {
                    $operation->setFromVersion($node->getAttribute('from'));
                    $operation->setToVersion($node->getAttribute('to'));
                }

                $operations[] = $operation;
            }
        }

        return $operations;
    }

    private function writeOperations()
    {
        if (!$this->isPreviousFileChecked && '' !== file_get_contents($this->targetFile)) {
            throw new \Exception(
                'A non empty operation file is already present (assumed not executed)'
            );
        }

        $this->isPreviousFileChecked = true;
        $this->document->save($this->targetFile);
    }
}
