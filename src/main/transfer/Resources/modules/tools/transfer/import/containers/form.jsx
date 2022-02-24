import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {withRouter} from '#/main/app/router'
import {param} from '#/main/app/config'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions as formActions} from '#/main/app/content/form/store'

import {ImportForm as ImportFormComponent} from '#/main/transfer/tools/transfer/import/components/form'
import {actions, selectors} from '#/main/transfer/tools/transfer/import/store'

const ImportForm = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      explanation: selectors.importExplanation(state),
      samples: selectors.importSamples(state),
      schedulerEnabled: param('schedulerEnabled')
    }),
    (dispatch) =>({
      updateProp(prop, value) {
        dispatch(formActions.updateProp(selectors.STORE_NAME + '.form', prop, value))
      },
      save() {
        return dispatch(formActions.saveForm(selectors.STORE_NAME + '.form', ['apiv2_transfer_import_create'])).then(response => {
          // request execution for the created import
          if (isEmpty(response.scheduler)) {
            dispatch(actions.execute(response.id)).then(() => response)
          }

          return response
        })
      }
    })
  )(ImportFormComponent)
)

export {
  ImportForm
}