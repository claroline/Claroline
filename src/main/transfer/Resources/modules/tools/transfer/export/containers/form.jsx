import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions as formActions} from '#/main/app/content/form/store'

import {actions, selectors} from '#/main/transfer/tools/transfer/export/store'
import {ExportForm as ExportFormComponent} from '#/main/transfer/tools/transfer/export/components/form'

const ExportForm = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      explanation: selectors.exportExplanation(state)
    }),
    (dispatch) =>({
      save() {
        return dispatch(formActions.saveForm(selectors.STORE_NAME + '.form', ['apiv2_transfer_export_create'])).then(response =>
          // request execution for the created export
          dispatch(actions.execute(response.id))
        )
      }
    })
  )(ExportFormComponent)
)

export {
  ExportForm
}