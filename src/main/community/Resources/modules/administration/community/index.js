
import {reducer} from '#/main/community/administration/community/store/reducer'
import {CommunityTool} from '#/main/community/administration/community/containers/tool'
import {CommunityMenu} from '#/main/community/administration/community/containers/menu'

/**
 * Users tool application.
 */
export default {
  component: CommunityTool,
  menu: CommunityMenu,
  store: reducer
}
