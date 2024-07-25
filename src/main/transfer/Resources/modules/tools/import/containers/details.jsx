import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions as formActions} from '#/main/app/content/form/store'

import {selectors} from '#/main/transfer/tools/import/store'
import {ImportDetails as ImportDetailsComponent} from '#/main/transfer/tools/import/components/details'

const ImportDetails = connect(
  state => ({
    path: toolSelectors.path(state),
    importFile: selectors.importFile(state)
  }),
  (dispatch) => ({
    openForm(importFile) {
      dispatch(formActions.reset(selectors.FORM_NAME, importFile, false))
    }
  })
)(ImportDetailsComponent)

export {
  ImportDetails
}
