<?php

namespace Claroline\CoreBundle\Tests\NewAPI;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\ValidatorProvider;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class SerializerProviderTest extends TransactionalTestCase
{
    /** @var SerializerProvider */
    private $provider;
    /** @var mixed[] */
    private $serializers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = $this->client->getContainer()->get('Claroline\AppBundle\API\SerializerProvider');
        $this->validator = $this->client->getContainer()->get('Claroline\AppBundle\API\ValidatorProvider');
        $this->schema = $this->client->getContainer()->get('Claroline\AppBundle\API\SchemaProvider');
        $this->sampleDir = $this->client->getContainer()->getParameter('claroline.api.sample.dir');

        $tokenStorage = $this->client->getContainer()->get('security.token_storage');
        $token = new AnonymousToken('key', 'anon.');
        $tokenStorage->setToken($token);
    }

    /**
     * @dataProvider getHandledClassesProvider
     *
     * @param string $class
     *
     * If json il malformed, a syntax error will be thrown
     */
    public function testSchema($class)
    {
        if ($this->schema->has($class)) {
            $schema = $this->schema->getSchema($class);
            $this->assertTrue(is_object($schema));
        } else {
            $this->markTestSkipped('No schema defined for class '.$class);
        }
    }

    /**
     * @dataProvider getHandledClassesProvider
     *
     * @param string $class
     */
    public function testSerializer($class)
    {
        $iterator = new \DirectoryIterator($this->schema->getSampleDirectory($class).'/json/valid/create');

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $originalData = \file_get_contents($file->getPathName());
                //let's test the deserializer
                $object = new $class();
                $object = $this->provider->deserialize(json_decode($originalData, true), $object);
                //can we serialize it ?
                $data = $this->provider->serialize($object);

                if ('Claroline\CoreBundle\Entity\User' === $class) {
                    $data['plainPassword'] = '123';
                }
                //is the result... valid ?
                $errors = $this->validator->validate($class, $data, ValidatorProvider::UPDATE);
                $this->assertTrue(0 === count($errors));
            }
        }
    }

    /**
     * @return [][]
     */
    public function getHandledClassesProvider()
    {
        parent::setUp();
        $provider = $this->client->getContainer()->get('Claroline\AppBundle\API\SerializerProvider');
        $schemaProvider = $this->client->getContainer()->get('Claroline\AppBundle\API\SchemaProvider');

        $classes = array_map(function ($serializer) use ($provider) {
            return [$provider->getSerializerHandledClass($serializer)];
        }, $provider->all());

        $classes = array_filter($classes, function ($class) use ($provider, $schemaProvider) {
            return $schemaProvider->has($class[0]) && $schemaProvider->getSampleDirectory($class[0]) && class_exists($class[0]);
        });

        return $classes;
    }
}
