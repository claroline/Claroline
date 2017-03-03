<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 1/16/17
 */

namespace Claroline\CoreBundle\Entity\Icon;

use Claroline\CoreBundle\Library\Utilities\Enum;

class IconSetTypeEnum extends Enum
{
    const RESOURCE_ICON_SET = 'resource_icon_set';
    const UTILITIES_ICON_SET = 'utilities_icon_set';
    const THEME_ICON_SET = 'theme_icon_set';
    // Add here any other icon set types necessary...
}
