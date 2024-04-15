import {connect} from 'react-redux'

import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {actions as toolActions, selectors as toolSelectors} from '#/main/core/tool/store'

import {EditorRights as EditorRightsComponent} from '#/main/core/tool/editor/components/rights'
import {selectors} from '#/main/core/tool/editor/store'

const EditorRights = connect(
  (state) => ({
    name: toolSelectors.name(state),
    contextType: toolSelectors.contextType(state),
    contextId: toolSelectors.contextId(state),
    contextData: toolSelectors.contextData(state),
    rights: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)).rights
  }),
  (dispatch) => ({
    updateRights(perms) {
      dispatch(formActions.update(selectors.STORE_NAME, perms))
    },
    refresh(toolName, updatedData, contextType) {
      dispatch(toolActions.load(toolName, updatedData, contextType))
      dispatch(toolActions.loadType(toolName, updatedData, contextType))
    }
  })
)(EditorRightsComponent)

export {
  EditorRights
}
