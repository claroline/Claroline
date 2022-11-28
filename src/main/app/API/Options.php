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
    /**
     * @deprecated use SerializerInterface::SERIALIZE_LIST
     */
    const SERIALIZE_LIST = SerializerInterface::SERIALIZE_LIST;
    /**
     * @deprecated use SerializerInterface::SERIALIZE_MINIMAL
     */
    const SERIALIZE_MINIMAL = SerializerInterface::SERIALIZE_MINIMAL;
    /**
     * @deprecated use SerializerInterface::ABSOLUTE_URL
     */
    const ABSOLUTE_URL = SerializerInterface::ABSOLUTE_URL;
    /**
     * @deprecated use SerializerInterface::REFRESH_UUID
     */
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

    /**
     * @deprecated
     */
    const WORKSPACE_VALIDATE_ROLES = 'workspace_validate_roles';
    // make created workspace a model
    const AS_MODEL = 'as_model';
    // avoid copying model (this is used by import)
    const NO_MODEL = 'no_model';

    //for role
    const SERIALIZE_ROLE_TOOLS_RIGHTS = 'serialize_role_tools_rights';

    /**
     * for resource node.
     *
     * @deprecated
     */
    const NO_RIGHTS = 'no_rights';

    // for transfer tool
    const WORKSPACE_IMPORT = 'workspace_import';
}
