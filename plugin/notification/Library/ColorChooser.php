<?php

namespace Icap\NotificationBundle\Library;

class ColorChooser
{
    protected $colorArray = array(
        '#1ABC9C',
        '#16A085',
        '#2ECC71',
        '#27AE60',
        '#3498DB',
        '#2980B9',
        '#9B59B6',
        '#8E44AD',
        '#34495E',
        '#2C3E50',
        '#22313F',
        '#F1C40F',
        '#F39C12',
        '#E67E22',
        '#D35400',
        '#E74C3C',
        '#C0392B',
        '#BDC3C7',
        '#95A5A6',
        '#7F8C8D',
        '#F17288',
        '#1DD2AF',
        '#19B698',
        '#40D47E',
        '#2CC36B',
        '#4AA3DF',
        '#2E8ECE',
        '#A66BBE',
        '#9B50BA',
        '#3D566E',
        '#354B60',
        '#F2CA27',
        '#F4A62A',
        '#E98B39',
        '#EC5E00',
        '#EA6153',
        '#D14233',
        '#CBD0D3',
        '#A3B1B2',
        '#8C9899',
        '#CB5A5E',
        '#0E7AC3',
        '#731046',
        '#C2237F',
        '#56BE8E',
        '#E0D90E',
        '#0095A6',
        '#E33938',
        '#FF3E00',
        '#C9112F',
        '#FF65B3',
        '#5BC4BE',
    );
    protected $alphabet = array(
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
    );
    protected $needleArray = array();
    protected $colorOjectArray = array();

    public function getColorForName($name)
    {
        $name = strtolower($name);

        if (array_key_exists($name, $this->colorOjectArray)) {
            return $this->colorOjectArray[$name]->color;
        } else {
            //Initially, we take as a needle the first letter of the name
            $needle = $name[0];

            //We test if the needle already exists, if so we add one more letter
            while (strlen($needle) < strlen($name) && in_array($needle, $this->needleArray)) {
                $needle .= $name[strlen($needle)];
            }

            //Stock needle in needle array
            array_push($this->needleArray, $needle);
            $colorForNeedle = $this->getColorForNeedle($needle);
            $this->colorOjectArray[$name] = (object) array('color' => $colorForNeedle, 'key' => $needle, 'name' => $name);

            return $colorForNeedle;
        }
    }

    public function setAlphabet($alphabet)
    {
        $this->alphabet = $alphabet;

        return $this;
    }

    public function setColorArray($colorArray)
    {
        $this->colorArray = $colorArray;

        return $this;
    }

    public function addColor($color)
    {
        if (!in_array($color, $this->colorArray)) {
            array_push($this->colorArray, $color);
        }

        return $this;
    }

    public function addColors($colorArray)
    {
        foreach ($colorArray as $color) {
            $this->addColor($color);
        }

        return $this;
    }

    public function getColorObjectArray()
    {
        return $this->colorOjectArray;
    }

    private function getColorForNeedle($needle)
    {
        $score = 0;
        for ($i = 0; $i < strlen($needle); ++$i) {
            $score += array_search($needle[$i], $this->alphabet);
        }
        $colorIndex = $score % count($this->colorArray);

        return $this->colorArray[$colorIndex];
    }
}
