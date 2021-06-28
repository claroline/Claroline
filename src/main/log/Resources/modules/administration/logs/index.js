import {reducer} from '#/main/log/administration/logs/store'
import {LogsTool} from '#/main/log/administration/logs/components/tool'
import {LogsMenu} from '#/main/log/administration/logs/components/menu'

/**
 * Logs administration tool application.
 */
export default {
  component: LogsTool,
  store: reducer,
  menu: LogsMenu
}
