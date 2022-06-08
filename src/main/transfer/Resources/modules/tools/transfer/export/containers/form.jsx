import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {withRouter} from '#/main/app/router'
import {param} from '#/main/app/config'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {actions, selectors} from '#/main/transfer/tools/transfer/export/store'
import {ExportForm as ExportFormComponent} from '#/main/transfer/tools/transfer/export/components/form'

const ExportForm = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      explanation: selectors.exportExplanation(state),
      schedulerEnabled: param('schedulerEnabled'),
      formData: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME + '.form')),
      isNew: formSelectors.isNew(formSelectors.form(state, selectors.STORE_NAME + '.form'))
    }),
    (dispatch) =>({
      updateProp(prop, value) {
        dispatch(formActions.updateProp(selectors.STORE_NAME + '.form', prop, value))
      },
      save(formData, isNew = false) {
        return dispatch(formActions.saveForm(selectors.STORE_NAME + '.form', isNew ? ['apiv2_transfer_export_create'] : ['apiv2_transfer_export_update', {id: formData.id}])).then(response => {
          // request execution for the created export
          if (isNew && isEmpty(response.scheduler)) {
            return dispatch(actions.execute(response.id)).then(() => response)
          }

          return response
        })
      }
    })
  )(ExportFormComponent)
)

export {
  ExportForm
}