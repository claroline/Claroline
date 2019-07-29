import {reducer} from '#/main/core/tools/users/store/reducer'
import {UsersTool} from '#/main/core/tools/users/containers/tool'
import {UsersMenu} from '#/main/core/tools/users/containers/menu'

export default {
  component: UsersTool,
  menu: UsersMenu,
  store: reducer
}