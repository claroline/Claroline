import {reducer} from '#/plugin/cursus/tools/cursus/store'
import {CursusTool} from '#/plugin/cursus/tools/cursus/components/tool'
import {CursusMenu} from '#/plugin/cursus/tools/cursus/components/menu'

export default {
  component: CursusTool,
  menu: CursusMenu,
  store: reducer
}
