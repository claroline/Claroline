<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BundleRecorder\Detector;

class FilterIterator extends \RecursiveFilterIterator
{
    public function accept()
    {
        $name = $this->current()->getFilename();

        return !in_array($name, array('Component', 'Bridge', 'Resources'))
            && 0 !== strpos($name, '.')
            && !preg_match('#Test#i', $name);
    }
}
