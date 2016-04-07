<?php

namespace Innova\VideoRecorderBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Innova\VideoRecorderBundle\DataFixtures\DefaultData;

class AdditionalInstaller extends BaseInstaller
{

  public function postInstall()
  {
      $default = new DefaultData();
      $default->load($this->container->get('claroline.persistence.object_manager'));
  }


}
