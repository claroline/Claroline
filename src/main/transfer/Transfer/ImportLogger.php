<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TransferBundle\Transfer;

use Claroline\AppBundle\API\Utils\ArrayUtils;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use Symfony\Component\Filesystem\Filesystem;

class ImportLogger implements LoggerInterface
{
    use LoggerTrait;

    private string $file;
    private ?array $cache = null;

    public function __construct(string $file)
    {
        $this->file = $file;

        if (!file_exists($file)) {
            $fs = new FileSystem();
            $fs->touch($file);
        }
    }

    public function log($level, $message, array $context = []): void
    {
        $separator = PHP_EOL;
        $data = $this->get();
        $time = date('m-d-y h:i:s').': ';
        $line = $time.$message;

        isset($data['log']) ?
            $data['log'] .= $separator.$line :
            $data['log'] = $line;

        $this->write($data);
    }

    public function set($property, $value): void
    {
        $data = $this->get();
        ArrayUtils::set($data, $property, $value);
        $this->write($data);
    }

    public function push($property, $value): void
    {
        $array = $this->get($property);

        if (!is_array($array)) {
            throw new \RuntimeException($property.' is not an array');
        }

        $array[] = $value;
        $this->set($property, $array);
    }

    public function append($property, $value, $separator = "\n"): void
    {
        $string = $this->get($property);

        if (!is_string($string)) {
            throw new \RuntimeException($property.' is not an string');
        }

        $this->set($property, $string.$separator.$value);
    }

    public function increment($property): void
    {
        $value = $this->get($property);

        if (!is_int($value)) {
            throw new \RuntimeException($property.' is not an integer');
        }

        $this->set($property, $value + 1);
    }

    public function end(): void
    {
        $this->set('end', true);
    }

    public function get(?string $property = null): array
    {
        $data = $this->cache ?: json_decode(file_get_contents($this->file), true);

        if ($property && $data) {
            return ArrayUtils::get($data, $property);
        }

        return $data ?: [];
    }

    private function write($data): void
    {
        $this->cache = $data;
        file_put_contents($this->file, json_encode($data));
    }
}
