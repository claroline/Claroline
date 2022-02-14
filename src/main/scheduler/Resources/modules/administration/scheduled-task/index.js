import {ScheduledTaskTool} from '#/main/scheduler/administration/scheduled-task/containers/tool'
import {ScheduledTaskMenu} from '#/main/scheduler/administration/scheduled-task/components/menu'
import {reducer} from '#/main/scheduler/administration/scheduled-task/store'

export default {
  component: ScheduledTaskTool,
  menu: ScheduledTaskMenu,
  store: reducer
}
