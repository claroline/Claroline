import {reducer} from '#/plugin/blog/resources/blog/store'
import {BlogResource} from '#/plugin/blog/resources/blog/containers/resource'

/**
 * Blog resource application.
 */
export default {
  component: BlogResource,
  store: reducer,
  styles: ['claroline-distribution-plugin-blog-blog-resource']
}
