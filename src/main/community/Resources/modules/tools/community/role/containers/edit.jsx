import {connect} from 'react-redux'

import {selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as workspaceSelectors} from '#/main/core/workspace/store'

import {actions, selectors} from '#/main/community/tools/community/role/store'
import {RoleEdit as RoleEditComponent} from '#/main/community/tools/community/role/components/edit'

const RoleEdit = connect(
  state => ({
    path: toolSelectors.path(state),
    role: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME)),

    workspace: toolSelectors.contextData(state) ? toolSelectors.contextData(state) : null,
    shortcuts: toolSelectors.contextData(state) ? workspaceSelectors.shortcuts(state) : null
  }),
  (dispatch) => ({
    reload(id, contextData) {
      dispatch(actions.open(id, contextData, true))
    }
  })
)(RoleEditComponent)

export {
  RoleEdit
}
