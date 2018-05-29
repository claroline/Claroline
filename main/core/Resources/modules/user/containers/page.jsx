import {connect} from 'react-redux'

import {currentUser} from '#/main/core/user/current'
import {actions} from '#/main/core/user/actions'
import {UserPage} from '#/main/core/user/components/page.jsx'
import {select as profileSelect} from '#/main/core/user/profile/selectors'

const authenticatedUser = currentUser()

/**
 * Connected container for users.
 *
 * Connects the <UserPage> component to a redux store.
 * If you don't use redux in your implementation @see Resource functional component.
 */
const UserPageContainer = connect(
  (state, ownProps) => ownProps.user ? ({
    user: ownProps.user,
    canEditProfile: authenticatedUser.roles.filter(r => ['ROLE_ADMIN'].concat(profileSelect.parameters(state)['roles_edition']).indexOf(r.name) > -1).length > 0
  }) : ({
    user: state.user,
    canEditProfile: authenticatedUser.roles.filter(r => ['ROLE_ADMIN'].concat(profileSelect.parameters(state)['roles_edition']).indexOf(r.name) > -1).length > 0
  }),
  dispatch => ({
    //edition
    updatePassword(user, password) {
      dispatch(actions.updatePassword(user, password))
    },
    updatePublicUrl(user, publicUrl) {
      dispatch(actions.updatePublicUrl(user, publicUrl, true))
    }
  })
)(UserPage)

export {
  UserPageContainer
}
