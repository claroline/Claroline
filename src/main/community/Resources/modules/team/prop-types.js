import {PropTypes as T} from 'prop-types'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {Role as RoleTypes} from '#/main/community/role/prop-types'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

const Team = {
  propTypes: {
    id: T.string,
    name: T.string,
    thumbnail: T.string,
    poster: T.string,
    meta: T.shape({
      description: T.string
    }),
    users: T.number,
    permissions: T.shape({
      open: T.bool,
      edit: T.bool,
      administrate: T.bool,
      delete: T.bool
    }),
    registration: T.shape({
      selfRegistration: T.bool.isRequired,
      selfUnregistration: T.bool.isRequired
    }),
    restrictions: T.shape({
      users: T.number
    }),
    publicDirectory: T.bool.isRequired,
    deletableDirectory: T.bool.isRequired,
    maxUsers: T.number,
    workspace: T.shape(
      WorkspaceTypes.propTypes
    ),
    role: T.shape(
      RoleTypes.propTypes
    ),
    managerRole: T.shape(
      RoleTypes.propTypes
    ),
    directory: T.shape(
      ResourceNodeTypes.propTypes
    )
  },
  defaultProps: {}
}

export {
  Team
}
