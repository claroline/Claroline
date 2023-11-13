import {connect} from 'react-redux'

import {ContextMenu as ContextMenuComponent} from '#/main/app/context/components/menu'
import {actions as menuActions, selectors as menuSelectors} from '#/main/app/layout/menu/store'

const ContextMenu = connect(
  (state) => ({
    section: menuSelectors.openedSection(state)
  }),
  (dispatch) => ({
    changeSection(section) {
      dispatch(menuActions.changeSection(section))
    }
  })
)(ContextMenuComponent)

export {
  ContextMenu
}
