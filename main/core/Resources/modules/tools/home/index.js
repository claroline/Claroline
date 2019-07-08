
import {HomeTool} from '#/main/core/tools/home/containers/tool'
import {HomeMenu} from '#/main/core/tools/home/containers/menu'

import {reducer} from '#/main/core/tools/home/store'

/**
 * HomeTool application.
 *
 * @constructor
 */
export const App = () => ({
  component: HomeTool,
  styles: ['claroline-distribution-main-core-home-tool']
})

export default {
  component: HomeTool,
  menu: HomeMenu,
  store: reducer,
  styles: ['claroline-distribution-main-core-home-tool']
}
