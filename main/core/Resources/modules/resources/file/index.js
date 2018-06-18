
import {FileCreation} from '#/main/core/resources/file/components/creation'
import {FileResource} from '#/main/core/resources/file/components/resource'
import {reducer} from '#/main/core/resources/text/reducer'

/**
 * File creation app.
 */
export const Creation = () => ({
  component: FileCreation
})

/**
 * Text resource application.
 */
export const App = () => ({
  component: FileResource,
  store: reducer,
  initialData: (initialData) => Object.assign({}, initialData, {
    resource: {
      node: initialData.resourceNode,
      evaluation: initialData.evaluation
    }
  })
})
