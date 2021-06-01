import {reducer} from '#/plugin/log/administration/logs/store'
import {LogsTool} from '#/plugin/log/administration/logs/containers/tool'

/**
 * Logs administration tool application.
 */
export default {
  component: LogsTool,
  store: reducer
}
