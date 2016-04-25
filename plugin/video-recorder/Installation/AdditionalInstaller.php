<?php

namespace Innova\VideoRecorderBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Innova\VideoRecorderBundle\DataFixtures\DefaultData;

class AdditionalInstaller extends BaseInstaller
{

  public function postInstall()
  {
      $om = $this->container->get('claroline.persistence.object_manager');
      // load plugin default data
      $default = new DefaultData();
      $default->load($om);
      // check libav-tools is installed
      $cmd = 'avconv -version';
      exec($cmd, $output, $return);
      // if error
      if(count($output) === 0 || $return !== 0){
        echo 'libav-tools not found... disable InnovaVideoRecorderPlugin';
        $plugin = $om->getRepository('ClarolineCoreBundle:Plugin')->findPluginByShortName('InnovaVideoRecorderBundle');
        $this->container->get('claroline.manager.plugin_manager')->disable($plugin);
      }
  }


}
