
import {TeamTool} from '#/plugin/team/tools/team/containers/tool'
import {TeamMenu} from '#/plugin/team/tools/team/components/menu'
import {reducer} from '#/plugin/team/tools/team/store'

export default {
  component: TeamTool,
  menu: TeamMenu,
  store: reducer
}
