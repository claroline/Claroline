import {reducer} from '#/plugin/web-resource/resources/web-resource/reducer'
import {WebResource} from '#/plugin/web-resource/resources/web-resource/components/resource'
import {WebResourceCreation} from '#/plugin/web-resource/resources/web-resource/components/creation'
/**
 * WebResource creation app.
 * TODO: should use the FileForm of '#/main/core/resources/file/actions/creation.jsx'
 */
export const Creation = () => ({
  component: WebResourceCreation
})

/**
 * WebResource application.
 */
export const App = () => ({
  component: WebResource,
  store: reducer,
  initialData: (initialData) => Object.assign({}, initialData, {
    resource: {
      node: initialData.resourceNode,
      evaluation: initialData.evaluation
    }
  })
})
