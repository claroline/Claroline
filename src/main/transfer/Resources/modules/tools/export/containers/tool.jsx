import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool'

import {reducer, selectors, actions} from '#/main/transfer/tools/export/store'
import {ExportTool as ExportToolComponent} from '#/main/transfer/tools/export/components/tool'

const ExportTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      contextData: toolSelectors.contextData(state),
      explanation: selectors.exportExplanation(state),
      canExport: hasPermission('export', toolSelectors.toolData(state))
    }),
    (dispatch) => ({
      open(exportFileId) {
        dispatch(actions.fetch(exportFileId))
      }
    })
  )(ExportToolComponent)
)

export {
  ExportTool
}
