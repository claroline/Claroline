
import {reducer} from '#/main/core/administration/users/store/reducer'
import {UsersTool} from '#/main/core/administration/users/containers/tool'

/**
 * Users tool application.
 */
export default {
  component: UsersTool,
  //menu: WorkspacesMenu,
  store: reducer
}
