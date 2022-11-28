import {reducer} from '#/main/community/tools/community/store/reducer'
import {CommunityTool} from '#/main/community/tools/community/containers/tool'
import {CommunityMenu} from '#/main/community/tools/community/containers/menu'
import {CommunityParameters} from '#/main/community/tools/community/containers/parameters'

export default {
  component: CommunityTool,
  menu: CommunityMenu,
  parameters: CommunityParameters,
  store: reducer,
  styles: ['claroline-distribution-main-community-tool']
}
