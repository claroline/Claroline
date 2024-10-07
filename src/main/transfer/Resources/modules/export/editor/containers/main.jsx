import {connect} from 'react-redux'

import isEmpty from 'lodash/isEmpty'
import {param} from '#/main/app/config'
import {withRouter} from '#/main/app/router'
import {actions, selectors} from '#/main/transfer/tools/export/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {ExportEditor as ExportEditorComponent} from '#/main/transfer/export/editor/components/main'

const ExportEditor = withRouter(
  connect(
    (state) => ({
      exportFile: selectors.exportFile(state),
      explanation: selectors.exportExplanation(state),
      schedulerEnabled: param('schedulerEnabled'),
      formData: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME))
    }),
    (dispatch) => ({
      openForm(exportFile) {
        dispatch(formActions.reset(selectors.FORM_NAME, exportFile, false))
      },
      resetForm(workspace) {
        dispatch(formActions.reset(selectors.FORM_NAME, {format: 'csv', workspace: workspace }, true))
      },
      updateProp(prop, value) {
        dispatch(formActions.updateProp(selectors.FORM_NAME, prop, value))
      },
      onSave(response) {
        if (isEmpty(response.scheduler)) {
          return dispatch(actions.execute(response.id))
        }
      }
    })
  )(ExportEditorComponent)
)

export {
  ExportEditor
}
