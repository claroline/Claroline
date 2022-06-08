import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions as formActions} from '#/main/app/content/form/store'

import {ImportDetails as ImportDetailsComponent} from '#/main/transfer/tools/transfer/import/components/details'
import {selectors} from '#/main/transfer/tools/transfer/import/store'

const ImportDetails = connect(
  state => ({
    path: toolSelectors.path(state),
    importFile: selectors.importFile(state)
  }),
  (dispatch) => ({
    openForm(importFile) {
      dispatch(formActions.reset(selectors.STORE_NAME + '.form', importFile, false))
    }
  })
)(ImportDetailsComponent)

export {
  ImportDetails
}
