
import {HomeTool} from '#/plugin/home/tools/home/containers/tool'
import {HomeMenu} from '#/plugin/home/tools/home/containers/menu'

import {reducer} from '#/plugin/home/tools/home/store'

/**
 * HomeTool application.
 */
export default {
  component: HomeTool,
  menu: HomeMenu,
  store: reducer,
  styles: ['claroline-distribution-plugin-home-home-tool']
}
