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
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

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
        $data = Yaml::parse(file_get_contents($path));
        try {
            $processedConfiguration = $processor->processConfiguration($manifestConfif,$data);
        } catch (\Exception $e) {
            var_dump(array($e->getMessage())) ;
        }
    }
} 