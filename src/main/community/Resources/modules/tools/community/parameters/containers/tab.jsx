import {connect} from 'react-redux'

import {actions as formActions} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ParametersTab as ParametersTabComponent} from '#/main/community/tools/community/parameters/components/tab'
import {selectors} from '#/main/community/tools/community/parameters/store'
import {actions as workspaceActions} from '#/main/core/workspace/store'

const ParametersTab = connect(
  (state) => ({
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state)
  }),
  (dispatch) => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(selectors.FORM_NAME, propName, propValue))
    },
    save(workspace) {
      dispatch(formActions.save(selectors.FORM_NAME, ['apiv2_workspace_update', {id: workspace.id}])).then(() => {
        dispatch(workspaceActions.reload(workspace))
      })
    }
  })
)(ParametersTabComponent)

export {
  ParametersTab
}
