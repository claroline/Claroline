<?php
/**
 * Created by PhpStorm.
 * User: ezs
 * Date: 13/01/14
 * Time: 15:41
 */

namespace Claroline\CoreBundle\Manager;


use Claroline\CoreBundle\Library\Transfert\ImporterInterface;
use Claroline\CoreBundle\Library\Transfert\ManifestConfiguration;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Util\XmlUtils;


class TransfertManager
{
    private $listImporters;


    public function __construct()
    {
        $this->listImporters = new ArrayCollection();
    }

    public function addImporter(ImporterInterface $importer)
    {
        return $this->listImporters->add($importer);
    }

    public function importWorkspace($path)
    {
        $manifestConfif = new ManifestConfiguration();
        $processor = new Processor();
        $doc = new \DOMDocument();
        $doc->load($path);
        $data = XmlUtils::convertDomElementToArray($doc->documentElement);
        $data = array('workspace' => $data);

        try {
            $processedConfiguration = $processor->processConfiguration($manifestConfif,$data);
            var_dump($processedConfiguration);
        } catch (\Exception $e) {

            var_dump(array($e->getMessage())) ;
        }
        $properties = $doc->getElementsByTagName('properties');
        $child = $properties->item(0);

        if($properties->length > 0) {
            //$this->addImporter('properties',new WorkspacePropertiesImporter($um));
        }

    }
} 