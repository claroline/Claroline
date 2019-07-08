<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\API;

final class ToolsOptions
{
    const EXCLUDED_TOOLS = [
        'all_my_badges',
        'formalibre_bulletin_tool',
        'formalibre_presence_tool',
        'formalibre_reservation_agenda',
        //'home',
        'inwicast_portal',
        'my-learning-objectives',
        'my_portfolios',
    ];

    const USER_CATEGORY = 'user';
    const TOOL_CATEGORY = 'tool';
    const NOTIFICATION_CATEGORY = 'notification';
}
