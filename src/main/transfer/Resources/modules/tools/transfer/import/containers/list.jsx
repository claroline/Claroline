import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {ImportList as ImportListComponent} from '#/main/transfer/tools/transfer/import/components/list'

const ImportList = connect(
  state => ({
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state)
  })
)(ImportListComponent)

export {
  ImportList
}
