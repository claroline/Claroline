import {connect} from 'react-redux'

import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ToolEditorPermissions as ToolEditorPermissionsComponent} from '#/main/core/tool/editor/components/permissions'
import {actions} from '#/main/core/tool/editor/store'

const ToolEditorPermissions = connect(
  (state) => ({
    name: toolSelectors.name(state),
    //loaded: toolSelectors.loaded(state),
    contextType: toolSelectors.contextType(state),
    contextData: toolSelectors.contextData(state),
    rights: formSelectors.value(formSelectors.form(state, toolSelectors.EDITOR_NAME), 'rights')
  }),
  (dispatch) => ({
    load(toolName, contextType, contextId) {
      return dispatch(actions.fetchRights(toolName, contextType, contextId)).then((rights) => {
        dispatch(formActions.load(toolSelectors.EDITOR_NAME, {rights: rights}))
      })
    },
    updateRights(perms) {
      dispatch(formActions.updateProp(toolSelectors.EDITOR_NAME, 'rights', perms))
    }
  })
)(ToolEditorPermissionsComponent)

export {
  ToolEditorPermissions
}
