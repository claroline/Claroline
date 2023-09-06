import {reducer} from '#/main/core/tools/trash/store'
import {TrashMenu} from '#/main/core/tools/trash/components/menu'
import {TrashTool} from '#/main/core/tools/trash/containers/tool'

/**
 * Resources trash tool application.
 */
export default {
  component: TrashTool,
  menu: TrashMenu,
  store: reducer
}
