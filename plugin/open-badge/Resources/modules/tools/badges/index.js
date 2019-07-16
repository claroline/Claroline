
import {OpenBadgeTool} from '#/plugin/open-badge/tools/badges/containers/tool'

import {reducer} from '#/plugin/open-badge/tools/badges/store/reducer'

/**
 * OpenBadge tool application.
 */
export default {
  component: OpenBadgeTool,
  store: reducer
}
