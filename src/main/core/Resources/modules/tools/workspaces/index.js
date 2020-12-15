import {WorkspacesTool} from '#/main/core/tools/workspaces/containers/tool'
import {WorkspacesMenu} from '#/main/core/tools/workspaces/containers/menu'

import {reducer} from '#/main/core/tools/workspaces/store'

/**
 * Workspaces tool application.
 */
export default {
  component: WorkspacesTool,
  menu: WorkspacesMenu,
  store: reducer
}
