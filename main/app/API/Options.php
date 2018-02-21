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

// todo : should be broken in multiple files or it will become very large with time.
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

    /*
     * Using this option, the serializers will return minmal data (no meta or restrictions)
     */
    const SERIALIZE_MINIMAL = 'serialize_minimal';

    /*******************************/
    /* SPECIFIC SERIALIZER OPTIONS */
    /*******************************/

    /*
     * Do we want to recursively serialize ?
     * currently used by: organization
     */
    const IS_RECURSIVE = 'is_recursive';

    /****************/
    /* CRUD OPTIONS */
    /****************/

    //do something better with these options

    const SOFT_DELETE = 'soft_delete';
    const THROW_VALIDATION_EXCEPTION = 'throw_validation_exception';
    const NO_VALIDATE = 'no_validate'; //if validation is too long for huge csv
    const NO_PERMISSION_CHECK = 'no_permission_check';

    //in user crud so the user can be logged automatically after creation
    //but it's probably not where the option should be located
    const USER_SELF_LOG = 'user_self_log';
    const ADD_PERSONAL_WORKSPACE = 'add_personal_workspace';
    const DESERIALIZE_FACET = 'deserialize_facet';
    const SERIALIZE_FACET = 'serialize_facet';
    const SEND_EMAIL = 'send_email';
    const ADD_NOTIFICATIONS = 'add_notifications';
    const PROFILE_SERIALIZE = 'profile_serialize';
    const REGISTRATION = 'registration';

    //for workspace
    const WORKSPACE_MODEL = 'workspace_model';

    //for role
    const SERIALIZE_COUNT_USER = 'serialize_count_user';

    //for serialize, do we want to (de)serialize objects in subtrees ?
    const DEEP_SERIALIZE = 'deep_serialize';
    const DEEP_DESERIALIZE = 'deep_deserialize';

    //file upload options
    const TEMPORARY_FILE = 'temporary_file';
    const PUBLIC_FILE = 'public_file';
    const PRIVATE_FILE = 'private_file';
}
