<?php

namespace HeVinci\CompetencyBundle\Tests\Transfer;

use HeVinci\CompetencyBundle\Util\RepositoryTestCase;

class ConverterTest extends RepositoryTestCase
{
    /**
     * @dataProvider frameworkProvider
     *
     * @param string $frameworkFileName
     */
    public function testConversionRoundTrip($frameworkFileName)
    {
        $container = $this->client->getContainer();
        $manager = $container->get('HeVinci\CompetencyBundle\Manager\CompetencyManager');
        $converter = $container->get('HeVinci\CompetencyBundle\Transfer\Converter');
        $file = __DIR__.'/../../Resources/format/valid/'.$frameworkFileName;

        $originalJson = file_get_contents($file);

        $framework = $converter->convertToEntity($originalJson);
        $this->om->persist($framework);
        $this->om->flush();

        $roundTripJson = $converter->convertToJson($manager->loadCompetency($framework));

        $this->assertJsonStringEqualsJsonString($originalJson, $roundTripJson);

        if ('non-ascii.json' === $frameworkFileName) {
            // previous check doesn't cover encoding problems (data is json-decoded before assertion).
            // the following is a raw assertion on "cleaned/normalized" strings
            $this->assertEquals(
                preg_replace('/\s/', '', $originalJson),
                preg_replace('/\s/', '', $roundTripJson)
            );
        }
    }

    public function testExistingScaleIsReusedWhenConvertingToEntity()
    {
        $scale = $this->persistScale('Civil service levels');
        $this->persistLevel('Level 1', $scale);
        $this->persistLevel('Level 2', $scale);
        $this->persistLevel('Level 3', $scale);
        $this->om->flush();

        $converter = $this->client->getContainer()->get('HeVinci\CompetencyBundle\Transfer\Converter');
        $file = __DIR__.'/../../Resources/format/valid/minimal-1.json';
        $framework = $converter->convertToEntity(file_get_contents($file));

        $this->assertEquals($scale, $framework->getScale());
    }

    public function frameworkProvider()
    {
        return [
            ['minimal-1.json'],
            ['intermediate-1.json'],
            ['intermediate-2.json'],
            ['full.json'],
            ['non-ascii.json'],
        ];
    }
}
