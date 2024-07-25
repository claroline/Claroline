import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {param} from '#/main/app/config'
import {withRouter} from '#/main/app/router'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions, selectors} from '#/main/transfer/tools/import/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {ImportForm as ImportFormComponent} from '#/main/transfer/tools/import/components/form'

const ImportForm = withRouter(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      explanation: selectors.importExplanation(state),
      samples: selectors.importSamples(state),
      schedulerEnabled: param('schedulerEnabled'),
      formData: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME)),
      isNew: formSelectors.isNew(formSelectors.form(state, selectors.FORM_NAME))
    }),
    (dispatch) =>({
      updateProp(prop, value) {
        dispatch(formActions.updateProp(selectors.FORM_NAME, prop, value))
      },
      save(formData, isNew = false) {
        return dispatch(formActions.saveForm(selectors.FORM_NAME, isNew ? ['apiv2_transfer_import_create'] : ['apiv2_transfer_import_update', {id: formData.id}])).then(response => {
          // request execution for the created import
          if (isNew && isEmpty(response.scheduler)) {
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
