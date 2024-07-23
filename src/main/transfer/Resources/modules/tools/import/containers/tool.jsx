import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool'

import {reducer, selectors, actions} from '#/main/transfer/tools/import/store'
import {ImportTool as ImportToolComponent} from '#/main/transfer/tools/import/components/tool'

const ImportTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state),
      contextData: toolSelectors.contextData(state),
      explanation: selectors.importExplanation(state),
      canImport: hasPermission('import', toolSelectors.toolData(state))
    }),
    (dispatch) => ({
      open(importFileId) {
        dispatch(actions.fetch(importFileId))
      },
      openForm(params) {
        dispatch(actions.open(selectors.FORM_NAME, Object.assign({format: 'csv'}, params)))
      }
    })
  )(ImportToolComponent)
)

export {
  ImportTool
}
