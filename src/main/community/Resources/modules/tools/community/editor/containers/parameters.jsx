import {connect} from 'react-redux'

import {actions as formActions} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'
import {selectors as parametersSelectors} from '#/main/core/tool/editor/store'

import {selectors} from '#/main/community/tools/community/store'
import {EditorParameters as EditorParametersComponent} from '#/main/community/tools/community/editor/components/parameters'

const EditorParameters = connect(
  (state) => ({
    loaded: toolSelectors.loaded(state),
    contextType: toolSelectors.contextType(state),
    contextId: toolSelectors.contextId(state),
    parameters: selectors.parameters(state)
  }),
  (dispatch) => ({
    load(parameters) {
      dispatch(formActions.load(parametersSelectors.STORE_NAME, {parameters: parameters}))
    },
    updateProp(prop, value) {
      dispatch(formActions.updateProp(parametersSelectors.STORE_NAME, 'parameters.'+prop, value))
    }
  })
)(EditorParametersComponent)

export {
  EditorParameters
}