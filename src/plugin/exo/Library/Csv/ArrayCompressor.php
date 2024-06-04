<?php

namespace UJM\ExoBundle\Library\Csv;

class ArrayCompressor
{
    public function __construct(
        private readonly string $bracket = '  ',
        private readonly string $separator = '|'
    ) {
    }

    public function compress(array $data): string
    {
        $string = $this->bracket[0].array_shift($data);

        foreach ($data as $el) {
            $string .= $this->separator.$el;
        }

        return $string.$this->bracket[1];
    }
}
