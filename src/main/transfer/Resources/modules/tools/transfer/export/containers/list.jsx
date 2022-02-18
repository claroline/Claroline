import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {ExportList as ExportListComponent} from '#/main/transfer/tools/transfer/export/components/list'

const ExportList = connect(
  state => ({
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state)
  })
)(ExportListComponent)

export {
  ExportList
}
