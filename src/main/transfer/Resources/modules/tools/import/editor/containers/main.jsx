import {connect} from 'react-redux'

import {param} from '#/main/app/config'
import {withRouter} from '#/main/app/router'
import {selectors} from '#/main/transfer/tools/import/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {ImportEditor as ImportEditorComponent} from '#/main/transfer/tools/import/editor/components/main'

const ImportEditor = withRouter(
  connect(
    (state) => ({
      explanation: selectors.importExplanation(state),
      samples: selectors.importSamples(state),
      importFile: selectors.importFile(state),
      schedulerEnabled: param('schedulerEnabled'),
      formData: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME))
    }),
    (dispatch) => ({
      openForm(importFile) {
        dispatch(formActions.reset(selectors.FORM_NAME, importFile, false))
      },
      updateProp(prop, value) {
        dispatch(formActions.updateProp(selectors.FORM_NAME, prop, value))
      }
    })
  )(ImportEditorComponent)
)

export {
  ImportEditor
}
