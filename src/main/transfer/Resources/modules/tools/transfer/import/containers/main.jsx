import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ImportMain as ImportMainComponent} from '#/main/transfer/tools/transfer/import/components/main'
import {actions, selectors} from '#/main/transfer/tools/transfer/import/store'

const ImportMain = connect(
  state => ({
    path: toolSelectors.path(state),
    contextData: toolSelectors.contextData(state),
    explanation: selectors.importExplanation(state),
    canImport: hasPermission('import', toolSelectors.toolData(state))
  }),
  dispatch => ({
    open(importFileId) {
      dispatch(actions.fetch(importFileId))
    },
    openForm(params) {
      dispatch(actions.open(selectors.STORE_NAME + '.form', Object.assign({format: 'csv'}, params)))
    }
  })
)(ImportMainComponent)

export {
  ImportMain
}
