import {registerModals} from '#/main/core/layout/modal'

// user modals
import {MODAL_REGISTER_USER_WORKSPACE, RegisterUserWorkspaceModal} from '#/main/core/workspace/user/modals/components/register-user-workspace.jsx'

// register user modals
registerModals([
  [MODAL_REGISTER_USER_WORKSPACE, RegisterUserWorkspaceModal]
])

export {
  MODAL_REGISTER_USER_WORKSPACE
}
