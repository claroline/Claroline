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
    public const SERIALIZE_LIST = SerializerInterface::SERIALIZE_LIST;
    /**
     * @deprecated use SerializerInterface::SERIALIZE_MINIMAL
     */
    public const SERIALIZE_MINIMAL = SerializerInterface::SERIALIZE_MINIMAL;
    /**
     * @deprecated use SerializerInterface::ABSOLUTE_URL
     */
    public const ABSOLUTE_URL = SerializerInterface::ABSOLUTE_URL;
    /**
     * @deprecated use SerializerInterface::REFRESH_UUID
     */
    public const REFRESH_UUID = SerializerInterface::REFRESH_UUID;

    /* SPECIFIC SERIALIZER OPTIONS */

    /*
     * Do we want to recursively serialize ?
     * currently used by: organization
     */
    public const IS_RECURSIVE = 'is_recursive';

    /* CRUD OPTIONS */

    public const SOFT_DELETE = 'soft_delete';
    public const FORCE_FLUSH = 'force_flush';
    public const PERSIST_TAG = 'persistTag'; // find a way to remove

    // for user
    public const NO_PERSONAL_WORKSPACE = 'no_personal_workspace';
    public const SERIALIZE_FACET = 'serialize_facet';
    public const NO_EMAIL = 'no_email';
    public const ADD_NOTIFICATIONS = 'add_notifications';
    public const VALIDATE_FACET = 'validate_facet';
    public const REGISTRATION = 'registration';

    /**
     * @deprecated
     */
    public const WORKSPACE_VALIDATE_ROLES = 'workspace_validate_roles';
    // make created workspace a model
    public const AS_MODEL = 'as_model';
    // avoid copying model (this is used by import)
    public const NO_MODEL = 'no_model';

    /**
     * for resource node.
     *
     * @deprecated
     */
    public const NO_RIGHTS = 'no_rights';

    // for transfer tool
    public const WORKSPACE_IMPORT = 'workspace_import';
}
