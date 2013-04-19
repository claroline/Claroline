<?php

namespace Claroline\CoreBundle\Library\Testing;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Workspace\TemplateBuilder;
use Claroline\CoreBundle\Library\Installation\Plugin\Loader;

abstract class FunctionalTestCase extends FixtureTestCase
{
    protected function logUser(User $user)
    {
        $this->client->request('GET', '/logout');
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->filter('#login-form button[type=submit]')->form();
        $form['_username'] = $user->getUsername();
        $form['_password'] = $user->getPlainPassword();

        return $this->client->submit($form);
    }

    /** @return Symfony\Component\Security\Core\SecurityContextInterface */
    protected function getSecurityContext()
    {
        return $this->client->getContainer()->get('security.context');
    }

    public function resetTemplate()
    {
        $container = $this->client->getContainer();
        $yml = $container->getParameter('claroline.param.templates_directory').'config.yml';
        $archpath = $container->getParameter('claroline.param.templates_directory').'default.zip';
        $archive = new \ZipArchive();
        $archive->open($archpath, \ZipArchive::OVERWRITE);
        $archive->addFile($yml, 'config.yml');
        $archive->close();
    }

    public function registerStubPlugins(array $pluginFqcns)
    {
        $container = $this->client->getContainer();
        $dbWriter = $container->get('claroline.plugin.recorder_database_writer');
        $pluginDirectory = $container->getParameter('claroline.param.stub_plugin_directory');
        $loader = new Loader($pluginDirectory);
        $validator = $container->get('claroline.plugin.validator');

        foreach ($pluginFqcns as $pluginFqcn) {
            $plugin = $loader->load($pluginFqcn);
            $validator->validate($plugin);
            $dbWriter->insert($plugin, $validator->getPluginConfiguration());
        }
    }

    public function addResourceTypeToTemplate($name)
    {
        $container = $this->client->getContainer();
        $builder = TemplateBuilder::fromTemplate(
            $container->getParameter('claroline.param.templates_directory')."default.zip"
        );
        $builder->addResourceType($name, 'ROLE_WS_MANAGER');
        $builder->write();
    }
}