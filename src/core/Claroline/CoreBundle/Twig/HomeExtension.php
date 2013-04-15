<?php

namespace Claroline\CoreBundle\Twig;

class HomeExtension extends \Twig_Extension
{
    protected $translator;

    /**
     * Get the injected services.
     *
     * @param Symfony\Component\Translation\Translator $translator The Translator service.
     *
     */
    public function __construct($translator)
    {
        $this->translator = $translator;
    }

    /**
     * Get filters of the service
     *
     * @return \Twig_Filter_Method
     */
    public function getFilters()
    {
        return array(
            'timeAgo' => new \Twig_Filter_Method($this, 'timeAgo'),
        );
    }

    /**
     * Get the elapsed time since $start to right now, with a transChoice() for translation in plural or singular.
     *
     * @param \DateTime $start The initial time.
     *
     * @return \String
     * @see Symfony\Component\Translation\Translator
     */
    public function timeAgo($start)
    {
        $end = new \DateTime("now");

        $interval = $start->diff($end);

        $formats = array("%Y", "%m", "%W", "%d", "%H", "%i", "%s");
        $translation["singular"] = array(
            "%Y" => "year",
            "%m" => "month",
            "%W" => "week",
            "%d" => "day",
            "%H" => "hour",
            "%i" => "minute",
            "%s" => "second"
        );
        $translation["plural"] = array(
            "%Y" => "years",
            "%m" => "months",
            "%W" => "weeks",
            "%d" => "days",
            "%H" => "hours",
            "%i" => "minutes",
            "%s" => "seconds"
        );

        foreach ($formats as $format) {
            if ($format == "%W") {

                $i = round($interval->format("%d") / 8); //fix for week that does not exist in DataInterval obj
            } else {
                $i = ltrim($interval->format($format), "0");
            }

            if ($i > 0) {
                return $this->translator->transChoice(
                    "%count% ".$translation["singular"][$format]." ago|%count% ".$translation["plural"][$format]." ago",
                    $i,
                    array('%count%' => $i),
                    "home"
                );
            }
        }

        return $this->translator->transChoice(
            "%count% second ago|%count% seconds ago",
            1,
            array('%count%' => 1),
            "home"
        );
    }

    /**
     * Get the name of the twig extention.
     *
     * @return \String
     */
    public function getName()
    {
        return 'home_extension';
    }
}

