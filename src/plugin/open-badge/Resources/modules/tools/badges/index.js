
import {BadgeTool} from '#/plugin/open-badge/tools/badges/containers/tool'
import {BadgeMenu} from '#/plugin/open-badge/tools/badges/containers/menu'

import {reducer} from '#/plugin/open-badge/tools/badges/store/reducer'

/**
 * OpenBadge tool application.
 */
export default {
  component: BadgeTool,
  menu: BadgeMenu,
  store: reducer,
  styles: ['claroline-distribution-plugin-open-badge-badges-tool']
}
