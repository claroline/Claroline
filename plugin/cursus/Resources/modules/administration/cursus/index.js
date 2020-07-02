import {reducer} from '#/plugin/cursus/administration/cursus/store'
import {CursusTool} from '#/plugin/cursus/administration/cursus/containers/tool'
import {CursusMenu} from '#/plugin/cursus/administration/cursus/components/menu'

export default {
  component: CursusTool,
  menu: CursusMenu,
  store: reducer
}