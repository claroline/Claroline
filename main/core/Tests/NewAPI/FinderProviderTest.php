<?php

namespace Claroline\CoreBundle\Tests\NewAPI;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class FinderProviderTest extends TransactionalTestCase
{
    /** @var SerializerProvider */
    private $provider;
    /** @var mixed[] */
    private $serializers;

    protected function setUp()
    {
        parent::setUp();
        $this->provider = $this->client->getContainer()->get('claroline.api.finder');
        $tokenStorage = $this->client->getContainer()->get('security.token_storage');
        $token = new AnonymousToken('key', 'anon.');
        $tokenStorage->setToken($token);
    }

    /**
     * @dataProvider getHandledClassesProvider
     *
     * Just test the generated sql is correct and there is no syntax error
     *
     * @param string $class
     */
    public function testFinder($class)
    {
        $finder = $this->provider->get($class);
        $filters = $finder->getFilters();
        $allFilters = [];

        foreach ($filters as $filterName => $filterOptions) {
            $filter = $this->buildFilter($filterName, $filterOptions);
            $allFilters = array_merge($allFilters, $filter);
        }

        $data = $this->provider->fetch($class, $allFilters);
        //empty array
        $this->assertTrue(is_array($data));
    }

    private function buildFilter($filterName, $filterOptions)
    {
        $type = $filterOptions['type'];

        if (is_array($type)) {
            $type = $type[0];
        }

        $value = 'abcdef';

        switch ($type) {
          case 'bool': $value = true; break;
          case 'boolean': $value = true; break;
          case 'integer': $value = 123; break;
          case 'datetime': $value = 'unevraidate'; break;
        }

        return [$filterName => $value];
    }

    /**
     * @return [][]
     */
    public function getHandledClassesProvider()
    {
        parent::setUp();
        $provider = $this->client->getContainer()->get('claroline.api.finder');

        $finders = array_filter($provider->all(), function ($finder) {
            return method_exists($finder, 'getFilters') ? count($finder->getFilters()) > 0 : false;
        });

        return array_map(function ($finder) use ($provider) {
            return [$finder->getClass()];
        }, $finders);
    }
}
