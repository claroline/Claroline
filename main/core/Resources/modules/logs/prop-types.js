import {PropTypes as T} from 'prop-types'

import {User as UserType} from '#/main/core/user/prop-types'
import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'
import {ResourceNode as ResourceNodeType} from '#/main/core/resource/prop-types'

const LogConnectPlatform = {
  propTypes: {
    id: T.string.isRequired,
    date: T.string.isRequired,
    user: T.shape(UserType.propTypes).isRequired,
    duration: T.number
  }
}

const LogConnectWorkspace = {
  propTypes: {
    id: T.string.isRequired,
    date: T.string.isRequired,
    user: T.shape(UserType.propTypes).isRequired,
    duration: T.number,
    workspace: T.shape(WorkspaceType.propTypes),
    workspaceName: T.string
  }
}

const LogConnectResource = {
  propTypes: {
    id: T.string.isRequired,
    date: T.string.isRequired,
    user: T.shape(UserType.propTypes).isRequired,
    duration: T.number,
    resource: T.shape(ResourceNodeType.propTypes),
    resourceName: T.string,
    resourceType: T.string
  }
}

export {
  LogConnectPlatform,
  LogConnectWorkspace,
  LogConnectResource
}
