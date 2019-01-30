import {connect} from 'react-redux'

import {actions as listActions} from '#/main/app/content/list/store/actions'

import {TrashTool as TrashToolComponent} from '#/main/core/tools/trash/components/tool'
import {selectors as toolSelectors} from '#/main/core/tool/store'

// TODO : make it available in desktop

const TrashTool = connect(
  (state) => ({
    workspace: toolSelectors.contextData(state)
  }),
  (dispatch) => ({
    invalidate() {
      dispatch(listActions.invalidateData('resources'))
    }
  })
)(TrashToolComponent)

export {
  TrashTool
}
