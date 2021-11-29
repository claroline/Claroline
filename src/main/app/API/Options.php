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
    /* SERIALIZER PROVIDER OPTIONS */

    const SERIALIZE_LIST = 'serialize_list';

    /*
     * Using this option, the serializers will return minimal data (no meta or restrictions)
     */
    const SERIALIZE_MINIMAL = 'serialize_minimal';

    const ABSOLUTE_URL = 'absolute_url';

    /* SPECIFIC SERIALIZER OPTIONS */

    /*
     * Do we want to recursively serialize ?
     * currently used by: organization
     */
    const IS_RECURSIVE = 'is_recursive';

    /* CRUD OPTIONS */

    //do something better with these options
    const SOFT_DELETE = 'soft_delete';
    const FORCE_FLUSH = 'force_flush';
    const PERSIST_TAG = 'persistTag';

    //for user
    const NO_PERSONAL_WORKSPACE = 'no_personal_workspace';
    const SERIALIZE_FACET = 'serialize_facet'; // TODO : replace by SERIALIZE_LIST / SERIALIZE_MINIMAL
    const NO_EMAIL = 'no_email';
    const ADD_NOTIFICATIONS = 'add_notifications';
    const VALIDATE_FACET = 'validate_facet';
    const REGISTRATION = 'registration';

    //for workspace
    const WORKSPACE_VALIDATE_ROLES = 'workspace_validate_roles';

    //for role
    const SERIALIZE_ROLE_TOOLS_RIGHTS = 'serialize_role_tools_rights';

    //for serialize, do we want to (de)serialize objects in subtrees ?
    const DEEP_SERIALIZE = 'deep_serialize';
    const DEEP_DESERIALIZE = 'deep_deserialize';

    const REFRESH_UUID = 'refresh_uuid';

    //for resource node
    const NO_RIGHTS = 'no_rights';

    //finder options
    const SQL_QUERY = 'sql_query';

    // for transfer tool
    const WORKSPACE_IMPORT = 'workspace_import';

    // for SchemaProvider
    const IGNORE_COLLECTIONS = 'ignore_collections';
}
