<?php

namespace HeVinci\CompetencyBundle\Transfer;

use HeVinci\CompetencyBundle\Util\RepositoryTestCase;

class ConverterTest extends RepositoryTestCase
{
    /**
     * @dataProvider frameworkProvider
     * @param string $frameworkFileName
     */
    public function testConversionRoundTrip($frameworkFileName)
    {
        $container = $this->client->getContainer();
        $manager = $container->get('hevinci.competency.competency_manager');
        $converter = $container->get('hevinci.competency.transfer_converter');
        $file = __DIR__ . '/../../Resources/format/valid/' . $frameworkFileName;

        $originalJson = file_get_contents($file);

        $framework = $converter->convertToEntity($originalJson);
        $this->om->persist($framework);
        $this->om->flush();

        $roundTripJson = $converter->convertToJson($manager->loadCompetency($framework));

        $this->assertJsonStringEqualsJsonString($originalJson, $roundTripJson);
    }

    public function frameworkProvider()
    {
        return [
            ['minimal-1.json'],
            ['intermediate-1.json'],
            ['intermediate-2.json'],
            ['full.json']
        ];
    }
}
