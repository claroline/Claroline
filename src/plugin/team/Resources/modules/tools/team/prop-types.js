import {PropTypes as T} from 'prop-types'

import {ResourceNode as ResourceNodeType} from '#/main/core/resource/prop-types'
import {Role as RoleType} from '#/main/community/prop-types'
import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'

const TeamParams = {
  propTypes: {
    id: T.string.isRequired,
    selfRegistration: T.bool.isRequired,
    selfUnregistration: T.bool.isRequired,
    publicDirectory: T.bool.isRequired,
    deletableDirectory: T.bool.isRequired,
    allowedTeams: T.number
  }
}

const Team = {
  propTypes: {
    id: T.string.isRequired,
    name: T.string,
    description: T.string,
    registration: T.shape({
      selfRegistration: T.bool.isRequired,
      selfUnregistration: T.bool.isRequired
    }),
    publicDirectory: T.bool.isRequired,
    deletableDirectory: T.bool.isRequired,
    maxUsers: T.number,
    workspace: T.shape(WorkspaceType.propTypes),
    role: T.shape(RoleType.propTypes),
    teamManagerRole: T.shape(RoleType.propTypes),
    directory: T.shape(ResourceNodeType.propTypes)
  }
}

export {
  TeamParams,
  Team
}