import {reducer} from '#/main/authentication/administration/authentication/store'
import {AuthenticationTool} from '#/main/authentication/administration/authentication/containers/tool'
import {AuthenticationMenu} from '#/main/authentication/administration/authentication/components/menu'

export default {
  component: AuthenticationTool,
  menu: AuthenticationMenu,
  store: reducer
}
