import {reducer} from '#/main/core/administration/connection-messages/store'
import {ConnectionMessagesTool} from '#/main/core/administration/connection-messages/containers/tool'
import {ConnectionMessagesMenu} from '#/main/core/administration/connection-messages/components/menu'
export default {
  component: ConnectionMessagesTool,
  menu: ConnectionMessagesMenu,
  store: reducer,
  styles: ['claroline-distribution-main-core-administration-connection-messages']
}
