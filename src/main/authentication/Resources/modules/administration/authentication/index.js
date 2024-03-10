import {reducer} from '#/main/authentication/administration/authentication/store'
import {AuthenticationMenu} from '#/main/authentication/administration/authentication/components/menu'
import {AuthenticationTool} from '#/main/authentication/administration/authentication/containers/tool'

export default {
  component: AuthenticationTool,
  menu: AuthenticationMenu,
  store: reducer
}
