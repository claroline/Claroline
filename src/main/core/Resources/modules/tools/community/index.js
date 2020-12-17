import {reducer} from '#/main/core/tools/community/store/reducer'
import {CommunityTool} from '#/main/core/tools/community/containers/tool'
import {CommunityMenu} from '#/main/core/tools/community/containers/menu'

export default {
  component: CommunityTool,
  menu: CommunityMenu,
  store: reducer
}
