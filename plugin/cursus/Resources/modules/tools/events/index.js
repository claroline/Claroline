import {reducer} from '#/plugin/cursus/tools/events/store'
import {EventsTool} from '#/plugin/cursus/tools/events/containers/tool'
import {EventsMenu} from '#/plugin/cursus/tools/events/containers/menu'

export default {
  component: EventsTool,
  menu: EventsMenu,
  store: reducer
}
