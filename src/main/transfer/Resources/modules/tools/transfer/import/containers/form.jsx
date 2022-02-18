import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions as formActions} from '#/main/app/content/form/store'

import {actions as logActions, selectors as logSelectors} from '#/main/transfer/tools/transfer/log/store'

import {ImportForm as ImportFormComponent} from '#/main/transfer/tools/transfer/import/components/form'
import {actions, selectors} from '#/main/transfer/tools/transfer/import/store'

const ImportForm = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      explanation: selectors.importExplanation(state),
      samples: selectors.importSamples(state),
      logs: logSelectors.log(state)
    }),
    (dispatch) =>({
      updateProp(prop, value) {
        dispatch(formActions.updateProp(selectors.STORE_NAME + '.form', prop, value))
      },
      resetLog() {
        dispatch(logActions.reset())
      },
      loadLog(transferId) {
        dispatch(logActions.load(transferId))
      },
      save() {
        return dispatch(formActions.saveForm(selectors.STORE_NAME + '.form', ['apiv2_transfer_import_create'])).then(response =>
          // request execution for the created import
          dispatch(actions.execute(response.id))
        )
      }
    })
  )(ImportFormComponent)
)

export {
  ImportForm
}