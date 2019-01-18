import {connect} from 'react-redux'

import {TrashTool as TrashToolComponent} from '#/main/core/tools/trash/components/tool'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions} from '#/main/core/tools/trash/store'

// TODO : make it available in desktop

const TrashTool = connect(
  (state) => ({
    workspace: toolSelectors.contextData(state)
  }),
  (dispatch) => ({
    restore(resourceNodes) {
      dispatch(actions.restore(resourceNodes))
    }
  })
)(TrashToolComponent)

export {
  TrashTool
}
