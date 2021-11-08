import {connect} from 'react-redux'

import {actions as formActions} from '#/main/app/content/form/store'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/core/tools/workspaces/store'
import {WorkspacesTool as WorkspacesToolComponent} from '#/main/core/tools/workspaces/components/tool'
import {hasPermission} from '#/main/app/security'

const WorkspacesTool = connect(
  (state) => ({
    path: toolSelectors.path(state),
    currentUser: securitySelectors.currentUser(state),
    canCreate: selectors.creatable(state),
    canArchive: hasPermission('archive', toolSelectors.toolData(state))
  }),
  (dispatch) => ({
    resetForm(formName, defaultProps, isNew = true) {
      dispatch(formActions.resetForm(formName, defaultProps, isNew))
    }
  })
)(WorkspacesToolComponent)

export {
  WorkspacesTool
}
