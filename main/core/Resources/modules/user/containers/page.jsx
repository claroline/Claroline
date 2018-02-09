import {connectPage} from '#/main/core/layout/page/connect'

import {actions} from '#/main/core/administration/user/user/actions'
import {UserPage} from '#/main/core/user/components/page.jsx'

/**
 * Connected container for users.
 *
 * Connects the <UserPage> component to a redux store.
 * If you don't use redux in your implementation @see Resource functional component.
 *
 * Requires the following reducers to be registered in your store (@see makePageReducer) :
 *   - modal
 *   - alerts
 *   - user
 */
const UserPageContainer = connectPage(
  (state, ownProps) => ownProps.user ? ({
    user: ownProps.user
  }) : ({
    user: state.user
  }),
  dispatch => ({
    //edition
    updatePassword(user, password) {
      dispatch(actions.changePassword(user, password))
    }
  })
)(UserPage)

export {
  UserPageContainer
}
