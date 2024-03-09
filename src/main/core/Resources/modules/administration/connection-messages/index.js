import {reducer} from '#/main/core/administration/connection-messages/store'
import {ConnectionMessagesTool} from '#/main/core/administration/connection-messages/containers/tool'

export default {
  component: ConnectionMessagesTool,
  store: reducer,
  styles: ['claroline-distribution-main-core-administration-connection-messages']
}
