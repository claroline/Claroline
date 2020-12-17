import {connect} from 'react-redux'

import {actions as formActions} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ParametersTab as ParametersTabComponent} from '#/main/core/tools/community/parameters/components/tab'
import {selectors} from '#/main/core/tools/community/parameters/store'

const ParametersTab = connect(
  (state) => ({
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state)
  }),
  (dispatch) => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(selectors.FORM_NAME, propName, propValue))
    }
  })
)(ParametersTabComponent)

export {
  ParametersTab
}
