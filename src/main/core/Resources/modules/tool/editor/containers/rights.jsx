import {connect} from 'react-redux'

import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {EditorRights as EditorRightsComponent} from '#/main/core/tool/editor/components/rights'
import {actions, selectors} from '#/main/core/tool/editor/store'

const EditorRights = connect(
  (state) => ({
    name: toolSelectors.name(state),
    //loaded: toolSelectors.loaded(state),
    contextType: toolSelectors.contextType(state),
    contextData: toolSelectors.contextData(state),
    rights: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME)).rights
  }),
  (dispatch) => ({
    load(toolName, contextType, contextId) {
      return dispatch(actions.fetchRights(toolName, contextType, contextId)).then((rights) => {
        dispatch(formActions.load(selectors.STORE_NAME, {rights: rights}))
      })
    },
    updateRights(perms) {
      dispatch(formActions.updateProp(selectors.STORE_NAME, 'rights', perms))
    }
  })
)(EditorRightsComponent)

export {
  EditorRights
}
