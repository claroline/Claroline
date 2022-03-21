import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {Users as UsersComponent} from '#/main/core/tools/community/user/components/users'
import {actions} from '#/main/core/tools/community/user/store'

const Users = connect(
  state => ({
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state)
  }),
  dispatch => ({
    unregister(users, workspace) {
      dispatch(actions.unregister(users, workspace))
    }
  })
)(UsersComponent)

export {
  Users
}
