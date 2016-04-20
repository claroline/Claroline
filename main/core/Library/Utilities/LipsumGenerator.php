<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Utilities;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.utilities.lipsum_generator")
 */
class LipsumGenerator
{
    /**
     * Generate some random texts.
     * If nbwords = 0, then it's somewhat random (from 5 to 500).
     * If $isFullText is set to TRUE, then their will be some punctuation.
     *
     * @param int  $nbWords
     * @param bool $isFullText
     * @param int  $maxChar    the miximal size of the string
     *
     * @return string
     */
    public function generateLipsum($nbWords = 0, $isFullText = false, $maxChar = 10000)
    {
        $words = $this->getArrayLipsum();
        $content = '';
        $endPhrase = array('?', '!', '.', '...');
        $loopBeforeEnd = 0;

        if ($nbWords == 0) {
            $nbWords = rand(5, 500);
        }

        for ($i = 0; $i < $nbWords; ++$i) {
            $nextPart = '';

            if ($loopBeforeEnd == 0) {
                $loopBeforeEnd = rand(3, 15);
            }

            --$loopBeforeEnd;

            if ($isFullText && $loopBeforeEnd == 0) {
                $nextPart .= "{$endPhrase[array_rand($endPhrase)]} ".ucfirst($words[array_rand($words)]).' ';
            } else {
                $nextPart .= ' '.$words[array_rand($words)];
            }

            if ((strlen($content) + strlen($nextPart)) < $maxChar - 1) {
                $content .= $nextPart;
            }

            ++$i;
        }

        if ($isFullText) {
            $content = ucfirst($content).'.';
        }

        return $content;
    }

    private function getArrayLipsum()
    {
        $lipsum = array('lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit',
            'curabitur', 'vel', 'hendrerit', 'libero', 'eleifend', 'blandit', 'nunc', 'ornare', 'odio',
            'ut', 'orci', 'gravida', 'imperdiet', 'nullam', 'purus', 'lacinia', 'a', 'pretium', 'quis',
            'congue', 'praesent', 'sagittis', 'laoreet', 'auctor', 'mauris', 'non', 'velit', 'eros',
            'dictum', 'proin', 'accumsan', 'sapien', 'nec', 'massa', 'volutpat', 'venenatis', 'sed', 'eu',
            'molestie', 'lacus', 'quisque', 'porttitor', 'ligula', 'dui', 'mollis', 'tempus', 'at', 'magna',
            'vestibulum', 'turpis', 'ac', 'diam', 'tincidunt', 'id', 'condimentum', 'enim', 'sodales', 'in',
            'hac', 'habitasse', 'platea', 'dictumst', 'aenean', 'neque', 'fusce', 'augue', 'leo', 'eget',
            'semper', 'mattis', 'tortor', 'scelerisque', 'nulla', 'interdum', 'tellus', 'malesuada', 'rhoncus',
            'porta', 'sem', 'aliquet', 'et', 'nam', 'suspendisse', 'potenti', 'vivamus', 'luctus', 'fringilla',
            'erat', 'donec', 'justo', 'vehicula', 'ultricies', 'varius', 'ante', 'primis', 'faucibus', 'ultrices',
            'posuere', 'cubilia', 'curae', 'etiam', 'cursus', 'aliquam', 'quam', 'dapibus', 'nisl', 'feugiat',
            'egestas', 'class', 'aptent', 'taciti', 'sociosqu', 'ad', 'litora', 'torquent', 'per', 'conubia',
            'nostra', 'inceptos', 'himenaeos', 'phasellus', 'nibh', 'pulvinar', 'vitae', 'urna', 'iaculis',
            'lobortis', 'nisi', 'viverra', 'arcu', 'morbi', 'pellentesque', 'metus', 'commodo', 'ut', 'facilisis',
            'felis', 'tristique', 'ullamcorper', 'placerat', 'aenean', 'convallis', 'sollicitudin', 'integer',
            'rutrum', 'duis', 'est', 'etiam', 'bibendum', 'donec', 'pharetra', 'vulputate', 'maecenas', 'mi',
            'fermentum', 'consequat', 'suscipit', 'aliquam', 'habitant', 'senectus', 'netus', 'fames',
            'quisque', 'euismod', 'curabitur', 'lectus', 'elementum', 'tempor', 'risus', 'cras', );

        return $lipsum;
    }
}
