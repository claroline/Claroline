<?php

namespace Claroline\CoreBundle\Tests\NewAPI;

use Claroline\AppBundle\API\TransferProvider;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

class TransferProviderTest extends TransactionalTestCase
{
    /** @var TransferProvider */
    private $provider;

    protected function setUp()
    {
        parent::setUp();
        $this->provider = $this->client->getContainer()->get('claroline.api.transfer');
    }

    /**
     * @dataProvider formatProvider
     *
     * @param string $format
     *
     * If json il malformed, a syntax error will be thrown
     */
    public function testActions($format)
    {
        $availableActions = $this->provider->getAvailableActions($format);

        foreach ($availableActions as $class) {
            foreach ($class as $action) {
                //we just check here the return is not null
                //(so json_decode worked and nothing crashed due to a bad schema)
                $this->assertTrue(null !== $action);
            }
        }
    }

    /**
     * @return string[]
     */
    public function formatProvider()
    {
        return [
          ['csv'],
          ['json'],
        ];
    }
}
