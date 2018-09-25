import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/components/withReducer'

import {RoleRegistrationModal as RoleRegistrationModalComponent} from '#/main/core/administration/workspace/workspace/modals/registration/components/modal'
import {selectors} from '#/main/core/administration/workspace/workspace/modals/registration/store/selectors'
import {reducer} from '#/main/core/administration/workspace/workspace/modals/registration/store/reducer'
import {actions} from '#/main/core/administration/workspace/workspace/actions'

const RoleRegistrationModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      roles: selectors.roles(state),
      selectedRole: selectors.selectedRole(state)
    }),
    (dispatch) => ({
      registerUsers(role, workspaces, users) {
        dispatch(actions.registerUsers(role, workspaces, users))
      },
      registerGroups(role, workspaces, groups) {
        dispatch(actions.registerGroups(role, workspaces, groups))
      }
    })
  )(RoleRegistrationModalComponent)
)

export {
  RoleRegistrationModal
}
