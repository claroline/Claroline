import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {WorkspacesMenu as WorkspacesMenuComponent} from '#/main/core/header/workspaces/components/menu'
import {actions, reducer, selectors} from '#/main/core/header/workspaces/store'

const WorkspacesMenu = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      personal: selectors.personal(state),
      history: selectors.history(state),
      creatable: selectors.creatable(state)
    }),
    (dispatch) => ({
      loadMenu() {
        dispatch(actions.fetchMenu())
      }
    })
  )(WorkspacesMenuComponent)
)

export {
  WorkspacesMenu
}
