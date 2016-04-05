<?php

namespace HeVinci\CompetencyBundle\Util;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class DataFixture
{
    protected $container;
    protected $output;
    protected $om;

    public function __construct(ContainerInterface $container, OutputInterface $output)
    {
        $this->container = $container;
        $this->output = $output;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    abstract protected function load();
    abstract protected function unload();

    protected function loadCsvData($file, $separator)
    {
        $dataset = [];

        if (false !== $handle = fopen($file, 'r')) {
            while (false !== $data = fgetcsv($handle, 1000, $separator)) {
                $dataset[] = $data;
            }

            fclose($handle);
        }

        return $dataset;
    }

    protected function flushSuites(array $data, $iterationCount, \Closure $callback)
    {
        $dataCount = count($data);
        $progress = new ProgressBar($this->output, $dataCount);
        $progress->start();
        $this->om->startFlushSuite();

        for ($i = 0, $max = $dataCount; $i < $max; ++$i) {
            $callback($data[$i]);
            $progress->advance();

            if ($i !== 0 && $i % $iterationCount === 0) {
                $this->om->endFlushSuite();
                $this->om->startFlushSuite();
            }
        }

        $this->om->endFlushSuite();
    }

    protected function createQueryBuilder()
    {
        return $this->container->get('doctrine.orm.entity_manager')
            ->createQueryBuilder();
    }
}
