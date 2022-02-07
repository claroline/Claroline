import {reducer} from '#/plugin/forum/resources/forum/store'
import {ForumResource} from '#/plugin/forum/resources/forum/containers/resource'
import {ForumMenu} from '#/plugin/forum/resources/forum/components/menu'

/**
 * Forum resource application.
 */
export default {
  component: ForumResource,
  menu: ForumMenu,
  store: reducer,
  styles: ['claroline-distribution-plugin-forum-forum-resource']
}
