import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {actions} from '#/main/core/user/actions'
import {UserPage} from '#/main/core/user/components/page'

/**
 * Connected container for users.
 *
 * Connects the <UserPage> component to a redux store.
 * If you don't use redux in your implementation @see Resource functional component.
 */
const UserPageContainer = withRouter(
  connect(
    (state, ownProps) =>  ({
      user: ownProps.user || state.user
    }),
    (dispatch) => ({
      updatePassword(user, password) {
        dispatch(actions.updatePassword(user, password))
      },
      updatePublicUrl(user, publicUrl) {
        dispatch(actions.updatePublicUrl(user, publicUrl, true))
      }
    })
  )(UserPage)
)

export {
  UserPageContainer
}
