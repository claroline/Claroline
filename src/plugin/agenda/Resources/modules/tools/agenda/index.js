
import {reducer} from '#/plugin/agenda/tools/agenda/store/reducer'
import {AgendaTool} from '#/plugin/agenda/tools/agenda/containers/tool'
import {AgendaMenu} from '#/plugin/agenda/tools/agenda/containers/menu'

export default {
  component: AgendaTool,
  menu: AgendaMenu,
  store: reducer,
  styles: ['claroline-distribution-plugin-agenda-agenda']
}
