<?php

namespace Claroline\PluginBundle\Tests\Fixtures;

use \vfsStream;

class VirtualPlugins
{
    public function buildVirtualPluginFiles()
    {
        vfsStream::setUp('virtual');

        $firstPlugin = array(
            'FirstPluginBundle' => array(
                'VendorXFirstPluginBundle.php' => '<?php namespace VendorX\FirstPluginBundle;
                                                   class VendorXFirstPluginBundle extends
                                                   \Claroline\PluginBundle\AbstractType\ClarolinePlugin
                                                   {}',
                'Resources' => array(
                    'config' => array(
                        'routing.yml' => ''
                        )
                    )
                )
            );
        $secondPlugin = array(
            'SecondPluginBundle' => array(
                'VendorXSecondPluginBundle.php' => '<?php namespace VendorX\SecondPluginBundle;
                                                    class VendorXSecondPluginBundle extends
                                                    \Claroline\PluginBundle\AbstractType\ClarolineApplication
                                                    {public function getLaunchers()
                                                    {
                                                        return array(
                                                            new \Claroline\GUIBundle\Widget\ApplicationLauncher
                                                            ("route_id_1", "trans_key_1", array("ROLE_TEST_1", "ROLE_TEST_2")),
                                                            new \Claroline\GUIBundle\Widget\ApplicationLauncher
                                                            ("route_id_2", "trans_key_2", array("ROLE_TEST_1")),
                                                        );
                                                    }}',
                'Resources' => array(
                    'config' => array(
                        'routing.yml' => ''
                        )
                    )
                )
            );
        $thirdPlugin = array(
            'ThirdPluginBundle' => array(
                'VendorYThirdPluginBundle.php' => '<?php namespace VendorY\ThirdPluginBundle;
                                                   class VendorYThirdPluginBundle extends
                                                   \Claroline\PluginBundle\AbstractType\ClarolinePlugin
                                                   {}'
                )
            );
        $fourthPlugin = array(
            'FourthPluginBundle' => array(
                'VendorYFourthPluginBundle.php' => '<?php namespace VendorY\FourthPluginBundle;
                                                   class VendorYFourthPluginBundle extends
                                                   \Claroline\PluginBundle\AbstractType\ClarolinePlugin
                                                   { public function getRoutingResourcesPaths()
                                                   {return "wrong/path/file.foo";}}'
                )
            );

        $structure = array(
            'plugin' => array(
                'VendorX' => array_merge($firstPlugin, $secondPlugin),
                'VendorY' => array_merge($thirdPlugin, $fourthPlugin)
                ),
            'config' => array(
                'namespaces' => '',
                'bundles' => '',
                'routing.yml' => ''
                )
            );

        vfsStream::create($structure, 'virtual');
    }
}