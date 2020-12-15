
import {ResourcesTool} from '#/main/core/tools/resources/containers/tool'
import {ResourcesMenu} from '#/main/core/tools/resources/containers/menu'

import {reducer} from '#/main/core/tools/resources/store'

/**
 * Resources tool application.
 *
 * @constructor
 */
export default {
  component: ResourcesTool,
  menu: ResourcesMenu,
  store: reducer
}
