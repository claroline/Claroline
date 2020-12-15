
import {reducer} from '#/main/core/administration/community/store/reducer'
import {CommunityTool} from '#/main/core/administration/community/containers/tool'
import {CommunityMenu} from '#/main/core/administration/community/containers/menu'

/**
 * Users tool application.
 */
export default {
  component: CommunityTool,
  menu: CommunityMenu,
  store: reducer
}
