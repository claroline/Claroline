
import {MessagingTool} from '#/plugin/message/tools/messaging/containers/tool'
import {MessagingParameters} from '#/plugin/message/tools/messaging/containers/parameters'
import {MessagingMenu} from '#/plugin/message/tools/messaging/components/menu'

import {reducer} from '#/plugin/message/tools/messaging/store'

export default {
  component: MessagingTool,
  menu: MessagingMenu,
  parameters: MessagingParameters,
  store: reducer
}
