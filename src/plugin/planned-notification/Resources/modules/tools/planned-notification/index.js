import {reducer} from '#/plugin/planned-notification/tools/planned-notification/store/reducer'
import {PlannedNotificationTool} from '#/plugin/planned-notification/tools/planned-notification/components/tool'
import {PlannedNotificationMenu} from '#/plugin/planned-notification/tools/planned-notification/components/menu'

export default {
  component: PlannedNotificationTool,
  menu: PlannedNotificationMenu,
  store: reducer
}
