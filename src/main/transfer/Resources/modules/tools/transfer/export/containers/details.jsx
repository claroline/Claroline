import {connect} from 'react-redux'

import {ExportDetails as ExportDetailsComponent} from '#/main/transfer/tools/transfer/export/components/details'
import {selectors} from '#/main/transfer/tools/transfer/export/store'

const ExportDetails = connect(
  state => ({
    exportFile: selectors.exportFile(state)
  })
)(ExportDetailsComponent)

export {
  ExportDetails
}
