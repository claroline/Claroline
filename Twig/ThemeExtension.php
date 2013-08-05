<?php

namespace Claroline\CoreBundle\Twig;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class ThemeExtension extends \Twig_Extension
{
    protected $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("doctrine.orm.default_entity_manager")
     * })
     */
    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    /**
     * Get filters of the service
     *
     * @return \Twig_Filter_Method
     */
    public function getFilters()
    {
        return array(
            'getThemePath' => new \Twig_Filter_Method($this, 'getThemePath')
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
    public function getThemePath($name)
    {
        $theme = $this->manager->getRepository("ClarolineCoreBundle:Theme\Theme")->findOneBy(array('name' => $name));

        if ($theme) {
            return $theme->getPath();
        }

        return "";
    }

    /**
     * Get the name of the twig extention.
     *
     * @return \String
     */
    public function getName()
    {
        return 'theme_extension';

    }
}
