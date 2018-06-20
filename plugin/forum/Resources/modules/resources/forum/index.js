import {ForumResource} from '#/plugin/forum/resources/forum/components/resource'
import {reducer} from '#/plugin/forum/resources/forum/reducer'


/**
 * Path resource application.
 *
 * @constructor
 */
export const App = () => ({
  component: ForumResource,
  store: reducer,
  styles: 'claroline-distribution-plugin-forum-forum-resource',
  initialData: initialData => Object.assign({}, initialData, {
    resource: {
      node: initialData.resourceNode,
      evaluation: initialData.evaluation
    },
    forumForm: {
      data: initialData.forum
    }
  })
})
