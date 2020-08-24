import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {actions, selectors} from '#/main/core/tools/transfer/store'
import {actions as logActions} from '#/main/core/tools/transfer/log/store'

import {ImportForm as ImportFormComponent} from '#/main/core/tools/transfer/import/components/form'

const ImportForm = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      explanation: selectors.explanation(state),
      logs: selectors.log(state),
      workspace: toolSelectors.contextData(state)
    }),
    (dispatch) =>({
      updateProp(prop, value, form, entity) {
        dispatch(actions.updateProp(prop, value, form, entity))
      },
      resetLog() {
        dispatch(logActions.reset())
      },
      loadLog(filename) {
        dispatch(logActions.load(filename))
      }
    })
  )(ImportFormComponent)
)

export {
  ImportForm
}