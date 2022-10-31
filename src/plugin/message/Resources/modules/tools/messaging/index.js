
import {MessagingTool} from '#/plugin/message/tools/messaging/containers/tool'
import {MessagingMenu} from '#/plugin/message/tools/messaging/components/menu'

import {reducer} from '#/plugin/message/tools/messaging/store'

export default {
  component: MessagingTool,
  menu: MessagingMenu,
  store: reducer
}
