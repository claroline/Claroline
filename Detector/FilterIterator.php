<?php

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
