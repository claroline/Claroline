import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {ExportDetails as ExportDetailsComponent} from '#/main/transfer/tools/transfer/export/components/details'
import {actions, selectors} from '#/main/transfer/tools/transfer/export/store'

const ExportDetails = connect(
  (state) => ({
    path: toolSelectors.path(state),
    exportFile: selectors.exportFile(state)
  }),
  (dispatch) => ({
    refresh(id) {
      dispatch(actions.execute(id)).then(() =>
        dispatch(actions.fetch(id))
      )
    },
    openForm(exportFile) {
      dispatch(formActions.reset(selectors.STORE_NAME + '.form', exportFile, false))
    }
  })
)(ExportDetailsComponent)

export {
  ExportDetails
}
