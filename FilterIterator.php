<?php

namespace Claroline\BundleRecorder;

class FilterIterator extends \RecursiveFilterIterator
{
    public function accept()
    {
        $name = $this->current()->getFilename();

        return !in_array($name, array('Component', 'Bridge', 'Resources', 'Tests', 'Test'))
            && 0 !== strpos($name, '.');
    }
}
