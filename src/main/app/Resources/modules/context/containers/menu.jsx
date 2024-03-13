import {connect} from 'react-redux'

import {ContextMenu as ContextMenuComponent} from '#/main/app/context/components/menu'
import {selectors, actions} from '#/main/app/context/store'

const ContextMenu = connect(
  (state) => ({
    path: selectors.path(state),
    opened: selectors.menuOpened(state),
    untouched: selectors.menuUntouched(state),
    //tools: contextSelectors.tools(state)
  }),
  (dispatch) => ({
    close() {
      dispatch(actions.close())
    }
  })
)(ContextMenuComponent)

export {
  ContextMenu
}
