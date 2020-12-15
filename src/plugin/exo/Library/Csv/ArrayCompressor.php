<?php

namespace UJM\ExoBundle\Library\Csv;

class ArrayCompressor
{
    public function __construct($bracket = '  ', $separator = '|')
    {
        $this->bracket = $bracket;
        $this->separator = $separator;
    }

    public function compress(array $data)
    {
        $string = $this->bracket[0].array_shift($data);

        foreach ($data as $el) {
            $string .= $this->separator.$el;
        }

        $string = $string.$this->bracket[1];

        return $string;
    }
}
