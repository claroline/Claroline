import {reducer} from '#/plugin/forum/resources/forum/store'
import {ForumResource} from '#/plugin/forum/resources/forum/containers/resource'

/**
 * Forum resource application.
 */
export default {
  component: ForumResource,
  store: reducer,
  styles: ['claroline-distribution-plugin-forum-forum-resource']
}
