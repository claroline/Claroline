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

use Claroline\AppBundle\API\Serializer\SerializerInterface;

// todo : should be broken in multiple files.
final class Options
{
    const SERIALIZE_LIST = SerializerInterface::SERIALIZE_LIST;
    const SERIALIZE_MINIMAL = SerializerInterface::SERIALIZE_MINIMAL;
    const ABSOLUTE_URL = SerializerInterface::ABSOLUTE_URL;
    const REFRESH_UUID = SerializerInterface::REFRESH_UUID;

    /* SPECIFIC SERIALIZER OPTIONS */

    /*
     * Do we want to recursively serialize ?
     * currently used by: organization
     */
    const IS_RECURSIVE = 'is_recursive';

    /* CRUD OPTIONS */

    const SOFT_DELETE = 'soft_delete';
    const FORCE_FLUSH = 'force_flush';
    const PERSIST_TAG = 'persistTag'; // find a way to remove

    //for user
    const NO_PERSONAL_WORKSPACE = 'no_personal_workspace';
    const SERIALIZE_FACET = 'serialize_facet';
    const NO_EMAIL = 'no_email';
    const ADD_NOTIFICATIONS = 'add_notifications';
    const VALIDATE_FACET = 'validate_facet';
    const REGISTRATION = 'registration';

    //for workspace
    const WORKSPACE_VALIDATE_ROLES = 'workspace_validate_roles';
    // make created workspace a model
    const AS_MODEL = 'as_model';
    // avoid copying model (this is used by import)
    const NO_MODEL = 'no_model';

    //for role
    const SERIALIZE_ROLE_TOOLS_RIGHTS = 'serialize_role_tools_rights';

    //for serialize, do we want to (de)serialize objects in subtrees ?
    const DEEP_SERIALIZE = 'deep_serialize';
    const DEEP_DESERIALIZE = 'deep_deserialize';

    //for resource node
    const NO_RIGHTS = 'no_rights';

    //finder options
    const SQL_QUERY = 'sql_query'; // to remove

    // for transfer tool
    const WORKSPACE_IMPORT = 'workspace_import';
}
