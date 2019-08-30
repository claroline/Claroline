
import {reducer} from '#/main/core/administration/community/store/reducer'
import {UsersTool} from '#/main/core/administration/community/containers/tool'

/**
 * Users tool application.
 */
export default {
  component: UsersTool,
  //menu: WorkspacesMenu,
  store: reducer
}
