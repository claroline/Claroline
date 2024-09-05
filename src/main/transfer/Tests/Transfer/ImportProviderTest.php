<?php

namespace Claroline\TransferBundle\Tests\Transfer;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\TransferBundle\Transfer\ImportProvider;

class ImportProviderTest extends TransactionalTestCase
{
    private ?ImportProvider $provider = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = $this->client->getContainer()->get(ImportProvider::class);
    }

    /**
     * @dataProvider formatProvider
     *
     * If json il malformed, a syntax error will be thrown
     */
    public function testActions(string $format): void
    {
        $availableActions = $this->provider->getAvailableActions($format, [], []);

        foreach ($availableActions as $class) {
            foreach ($class as $action) {
                // we just check here the return is not null
                // (so json_decode worked and nothing crashed due to a bad schema)
                $this->assertTrue(null !== $action);
            }
        }
    }

    /**
     * @return string[]
     */
    public function formatProvider(): array
    {
        return [
            ['csv'],
        ];
    }
}
