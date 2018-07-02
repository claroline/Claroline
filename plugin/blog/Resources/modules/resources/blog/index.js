import {BlogContainer} from '#/plugin/blog/resources/blog/player/components/resource.jsx'
import {reducer} from '#/plugin/blog/resources/blog/store/reducer.js'

/**
 * Blog resource application.
 *
 * @constructor
 */
export const App = () => ({
  component: BlogContainer,
  store: reducer,
  styles: 'claroline-distribution-plugin-blog-blog-resource',
  initialData: initialData => Object.assign({}, initialData, {
    user: initialData.user,
    blog: {
      data: {
        id: initialData.blog.id,
        title: initialData.blog.title,
        authors: initialData.authors,
        archives: initialData.archives,
        tags: initialData.tags,
        options: {
          data: initialData.blog.options
        }
      }
    },
    posts: {
      pageSize: initialData.blog.options.postPerPage
    },
    resource: {
      node: initialData.resourceNode
    }
  })
})