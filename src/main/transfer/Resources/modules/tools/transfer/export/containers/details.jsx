import {connect} from 'react-redux'

import {ExportDetails as ExportDetailsComponent} from '#/main/transfer/tools/transfer/export/components/details'
import {actions, selectors} from '#/main/transfer/tools/transfer/export/store'

const ExportDetails = connect(
  (state) => ({
    exportFile: selectors.exportFile(state)
  }),
  (dispatch) => ({
    refresh(id) {
      dispatch(actions.execute(id)).then(() =>
        dispatch(actions.fetch(id))
      )
    }
  })
)(ExportDetailsComponent)

export {
  ExportDetails
}
