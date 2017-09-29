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

use Claroline\CoreBundle\API\FinderProvider;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 * @DI\Tag("twig.extension")
 */
class FinderExtension extends \Twig_Extension
{
    /** @var FinderProvider */
    private $finder;

    /**
     * @DI\InjectParams({
     *     "finder" = @DI\Inject("claroline.API.finder")
     * })
     *
     * @param FinderProvider $finder
     */
    public function __construct(FinderProvider $finder)
    {
        $this->finder = $finder;
    }

    public function getFunctions()
    {
        return [
            'search' => new \Twig_Function_Method($this, 'search'),
        ];
    }

    public function getName()
    {
        return 'searcher_extension';
    }

    public function search($class, $queryOptions, $serializerOptions)
    {
        return $this->finder->search(
            $class,
            $queryOptions,
            $serializerOptions
        )['data'];
    }
}
