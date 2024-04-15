import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'

import {actions as toolActions, selectors as toolSelectors} from '#/main/core/tool/store'
import {EditorParameters as EditorParametersComponent} from '#/main/core/tool/editor/components/parameters'
import {reducer, selectors} from '#/main/core/tool/editor/store'

const EditorParameters = connect(
  (state) => ({
    name: toolSelectors.name(state),
    contextType: toolSelectors.contextType(state),
    contextId: toolSelectors.contextId(state),
  }),
  (dispatch) => ({
    refresh(toolName, updatedData, contextType) {
      dispatch(toolActions.load(toolName, updatedData, contextType))
      dispatch(toolActions.loadType(toolName, updatedData, contextType))
    }
  })
)(EditorParametersComponent)

export {
  EditorParameters
}
