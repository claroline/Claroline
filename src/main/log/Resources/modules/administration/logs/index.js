import {reducer} from '#/main/log/administration/logs/store'
import {LogsTool} from '#/plugin/tag/administration/tags/containers/tool'

/**
 * Logs administration tool application.
 */
export default {
  component: LogsTool,
  store: reducer
}
