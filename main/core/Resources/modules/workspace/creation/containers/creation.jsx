import {connect} from 'react-redux'

import {CreationForm} from '#/main/core/workspace/creation/components/creation'

const ConnectedWorkspaceCreationContainer = connect(
  null,
  null
)(CreationForm)

export {
  ConnectedWorkspaceCreationContainer as WorkspaceCreation
}
