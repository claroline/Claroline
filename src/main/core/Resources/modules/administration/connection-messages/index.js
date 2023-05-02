import {ConnectionMessagesTool} from '#/main/core/administration/connection-messages/components/tool'
import {ConnectionMessagesMenu} from '#/main/core/administration/connection-messages/components/menu'
import {reducer} from '#/main/core/administration/connection-messages/store'
export default {
  component: ConnectionMessagesTool,
  menu: ConnectionMessagesMenu,
  store: reducer
}
