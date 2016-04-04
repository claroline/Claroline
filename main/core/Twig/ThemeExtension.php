<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Twig;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;

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
            'getThemePath' => new \Twig_Filter_Method($this, 'getThemePath'),
            'asset_exists' =>  new \Twig_Function_Method($this, 'assetExists')
        );
    }

    /**
     * Get the elapsed time since $start to right now, with a transChoice() for translation in plural or singular.
     *
     * @param \DateTime $start The initial time.
     *
     * @return \String
     *                 @see Symfony\Component\Translation\Translator
     */
    public function getThemePath($name)
    {
        $theme = $this->manager->getRepository("ClarolineCoreBundle:Theme\Theme")->findOneBy(array('name' => $name));

        if ($theme) {
            return $theme->getPath();
        }

        return "";
    }

    public function assetExists($path)
    {
        $webRoot = realpath($this->kernel->getRootDir() . '/../web/');
        $toCheck = realpath($webRoot . $path);

        // check if the file exists
        if (!is_file($toCheck))
        {
            return false;
        }

        // check if file is well contained in web/ directory (prevents ../ in paths)
        if (strncmp($webRoot, $toCheck, strlen($webRoot)) !== 0)
        {
            return false;
        }

        return true;
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
