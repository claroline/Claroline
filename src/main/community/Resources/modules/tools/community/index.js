import {reducer} from '#/main/community/tools/community/store/reducer'
import {CommunityTool} from '#/main/community/tools/community/containers/tool'
import {CommunityMenu} from '#/main/community/tools/community/containers/menu'

export default {
  component: CommunityTool,
  menu: CommunityMenu,
  store: reducer
}
