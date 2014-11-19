<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

    public function addOperation(Operation $operation, $append = true)
    {
        $this->log("Logging {$operation->getType()} action in the operation file...");
        $opNode = $this->document->createElement($operation->getType());
        $opNode->nodeValue = $operation->getBundleFqcn();
        $opNode->setAttribute('type', $operation->getBundleType());

        if ($operation->getType() === Operation::UPDATE) {
            $opNode->setAttribute('from', $operation->getFromVersion());
            $opNode->setAttribute('to', $operation->getToVersion());
        }

        if (!$append) {
            $nextNode = $this->findNextNode($operation);
            $this->rootElement->insertBefore($opNode, $nextNode);
        } else {
            $this->rootElement->appendChild($opNode);
        }

        $this->writeOperations();
    }

    private function findNextNode(Operation $operation)
    {
        $dependencies = $operation->getDependencies();
        $prevNode = null;
        $saveFurthest = 0;

        if (isset($dependencies[0])) {
            foreach ($dependencies[0] as $dependency) {
                $foundDep = false;
                $i = 0;
    
                foreach ($this->rootElement->childNodes as $childNode) {
                    $i++;
                    if ($childNode->nodeValue === $dependency) {
                        if ($i > $saveFurthest) {
                            $prevNode = $childNode;
                            $saveFurthest = $i;
                        }
                    }
                }
            }
        }

        if ($prevNode) {
            return $prevNode->nextSibling;
        }

        return $this->rootElement->firstChild;
    }

    public function getOperations()
    {
        if ($this->isFileEmpty()) {
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
        if (!$this->isPreviousFileChecked && !$this->isFileEmpty()) {
            throw new \Exception(
                'A non empty operation file is already present (assumed not executed)'
            );
        }

        $this->isPreviousFileChecked = true;
        $this->document->save($this->targetFile);
    }
}
