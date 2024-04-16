import {reducer} from '#/plugin/cursus/tools/presence/store'
import {PresenceTool} from '#/plugin/cursus/tools/presence/components/tool'
import {PresenceMenu} from '#/plugin/cursus/tools/presence/components/menu'
export default {
  component: PresenceTool,
  menu: PresenceMenu,
  store: reducer
}
