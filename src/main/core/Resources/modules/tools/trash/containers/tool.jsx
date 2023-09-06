import {connect} from 'react-redux'

import {TrashTool as TrashToolComponent} from '#/main/core/tools/trash/components/tool'
import {selectors as toolSelectors} from '#/main/core/tool/store'

const TrashTool = connect(
  (state) => ({
    workspace: toolSelectors.contextData(state)
  })
)(TrashToolComponent)

export {
  TrashTool
}
