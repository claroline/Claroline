<?php

namespace Innova\VideoRecorderBundle\Twig;

class VideoRecorderExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'video_recorder_extension';
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('hmsTime', array($this, 'secondToHmsFilter')),
        );
    }
    public function secondToHmsFilter($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds - ($hours * 3600)) / 60);
        $seconds = $seconds - ($hours * 3600) - ($minutes * 60);

        // round seconds
        $seconds = round($seconds * 100) / 100;
        $result = '';
        if ($hours > 0) {
            $result .= $hours.' h ';
        }
        if ($minutes > 0) {
            $result .= $minutes.' min ';
        }
        if ($seconds > 0) {
            $result .= $seconds.' s';
        }

        return $result;
    }
}
