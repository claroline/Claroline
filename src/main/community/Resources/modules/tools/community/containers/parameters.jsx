import {connect} from 'react-redux'

import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'
import {selectors as parametersSelectors} from '#/main/core/tool/modals/parameters/store'

import {selectors} from '#/main/community/tools/community/store'
import {CommunityParameters as CommunityParametersComponent} from '#/main/community/tools/community/components/parameters'

const CommunityParameters = connect(
  (state) => ({
    contextType: toolSelectors.contextType(state),
    contextData: toolSelectors.contextData(state),
    pendingChanges: formSelectors.pendingChanges(formSelectors.form(state, parametersSelectors.STORE_NAME)),
    parameters: selectors.parameters(state)
  }),
  (dispatch) => ({
    load(parameters) {
      dispatch(formActions.updateProp(parametersSelectors.STORE_NAME, 'parameters', parameters))
    },
    updateProp(prop, value) {
      dispatch(formActions.updateProp(parametersSelectors.STORE_NAME, 'parameters.'+prop, value))
    }
  })
)(CommunityParametersComponent)

export {
  CommunityParameters
}