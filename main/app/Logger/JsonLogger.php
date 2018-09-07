<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\Logger;

use Claroline\AppBundle\API\Utils\ArrayUtils;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Filesystem\Filesystem;

class JsonLogger implements LoggerInterface
{
    private $file;
    private $cache;
    private $utils;

    public function __construct($file)
    {
        $this->utils = new ArrayUtils();
        $this->file = $file;
        $this->cache = null;

        if (!file_exists($file)) {
            $fs = new FileSystem();
            $fs->touch($file);
        }
    }

    public function set($property, $value)
    {
        $data = $this->get();
        $this->utils->set($data, $property, $value);
        $this->write($data);
    }

    public function push($property, $value)
    {
        $array = $this->get($property);

        if (!is_array($array)) {
            throw new \RuntimeException($property.' is not an array');
        }

        $array[] = $value;
        $this->set($property, $array);
    }

    public function append($property, $value, $separator = "\n")
    {
        $string = $this->get($property);

        if (!is_string($string)) {
            throw new \RuntimeException($property.' is not an string');
        }

        $this->set($property, $string.$separator.$value);
    }

    public function increment($property)
    {
        $value = $this->get($property);

        if (!is_int($value)) {
            throw new \RuntimeException($property.' is not an integer');
        }

        $this->set($property, $value + 1);
    }

    public function write($data)
    {
        $this->cache = $data;
        file_put_contents($this->file, json_encode($data));
    }

    public function log($level, $message, array $context = [])
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

    public function info($message, array $context = [])
    {
        $this->log(LogLevel::INFO, $message);
    }

    public function emergency($message, array $context = [])
    {
    }

    public function alert($message, array $context = [])
    {
    }

    public function critical($message, array $context = [])
    {
    }

    public function error($message, array $context = [])
    {
    }

    public function warning($message, array $context = [])
    {
    }

    public function debug($message, array $context = [])
    {
    }

    public function notice($message, array $context = [])
    {
    }

    public function end()
    {
        $this->set('end', true);
    }

    public function get($property = null)
    {
        $data = $this->cache ? $this->cache : json_decode(file_get_contents($this->file), true);

        if ($property) {
            return $this->utils->get($data, $property);
        }

        return $data ? $data : [];
    }
}
