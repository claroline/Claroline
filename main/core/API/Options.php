<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API;

final class Options
{
    /*******************************/
    /* SERIALIZER PROVIDER OPTIONS */
    /*******************************/

    /*
     * Using this option, the serializer provider won't fetch any data from the database
     * even if an id or an uuid exists
     */
    const NO_FETCH = 'no_fetch';

    /*******************************/
    /* SPECIFIC SERIALIZER OPTIONS */
    /*******************************/

    /*
     * Do we want to recursively serialize ?
     * currently used by: organization
     */
    const IS_RECURSIVE = 'is_recursive';
}
