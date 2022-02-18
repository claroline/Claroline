import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ExportMain as ExportMainComponent} from '#/main/transfer/tools/transfer/export/components/main'
import {actions, selectors} from '#/main/transfer/tools/transfer/export/store'

const ExportMain = connect(
  state => ({
    path: toolSelectors.path(state),
    contextData: toolSelectors.contextData(state),
    explanation: selectors.exportExplanation(state)
  }),
  dispatch => ({
    open(exportFileId) {
      dispatch(actions.fetch(exportFileId))
    },
    openForm(params) {
      dispatch(actions.open(selectors.STORE_NAME + '.form', Object.assign({format: 'csv'}, params)))
    }
  })
)(ExportMainComponent)

export {
  ExportMain
}
