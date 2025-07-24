<?php

namespace Claroline\TransferBundle\Component\Importer;

use OpenSpout\Reader\CSV\Reader as CSVReader;
use OpenSpout\Reader\ODS\Reader as ODSReader;
use OpenSpout\Reader\XLSX\Reader as XLSXReader;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class FileReader implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly string $filePath,
        private readonly array $options = []
    ) {
    }

    public function read(): iterable
    {
        $extension = pathinfo($this->filePath, PATHINFO_EXTENSION);
        $reader = match ($extension) {
            'csv' => new CSVReader(),
            'ods' => new ODSReader(),
            'xlsx' => new XLSXReader(),
            default => throw new \RuntimeException('File not supported'),
        };

        $reader->open($this->filePath);

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                // do stuff with the row
                yield $row->getCells();
            }
        }

        $reader->close();
    }
}
