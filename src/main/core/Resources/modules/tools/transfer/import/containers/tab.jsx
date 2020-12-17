import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions, selectors} from '#/main/core/tools/transfer/store'

import {ImportTab as ImportTabComponent} from '#/main/core/tools/transfer/import/components/tab'

const ImportTab = connect(
  state => ({
    path: toolSelectors.path(state),
    explanation: selectors.explanation(state)
  }),
  dispatch =>({
    openForm(params) {
      dispatch(actions.open(selectors.STORE_NAME + '.import', Object.assign({format: 'csv'}, params)))
    }
  })
)(ImportTabComponent)

export {
  ImportTab
}
