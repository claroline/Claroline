import {reducer} from '#/main/core/tools/trash/store'
import {TrashTool} from '#/main/core/tools/trash/containers/tool'

/**
 * Resources trash tool application.
 */
export default {
  component: TrashTool,
  store: reducer
}
