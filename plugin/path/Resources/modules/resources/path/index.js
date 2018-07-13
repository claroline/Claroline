import {PathResource} from '#/plugin/path/resources/path/components/resource'
import {reducer} from '#/plugin/path/resources/path/reducer'

/**
 * Path resource application.
 *
 * @constructor
 */
export const App = () => ({
  component: PathResource,
  store: reducer,
  styles: 'claroline-distribution-plugin-path-path-resource',
  initialData: initialData => Object.assign({}, initialData, {
    resource: {
      node: initialData.resourceNode,
      evaluation: initialData.evaluation
    },
    summary: {
      opened: initialData.path.display.openSummary,
      pinned: initialData.path.display.openSummary
    },
    pathForm: {
      originalData: initialData.path,
      data: initialData.path
    }
  })
})
