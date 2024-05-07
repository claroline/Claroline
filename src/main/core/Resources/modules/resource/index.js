import {route} from '#/main/core/resource/routing'
import {ResourceMain as Resource} from '#/main/core/resource/components/main'
import {ResourcePage} from '#/main/core/resource/components/page'
import {ResourceOverview} from '#/main/core/resource/components/overview'
import {ResourceEditor} from '#/main/core/resource/editor/containers/main'
import {selectors} from '#/main/core/resource/store'

function declareResource(ResourceComponent, additional) {
  return {
    component: ResourceComponent,
    ...additional
  }
}

/**
 * Exposes public parts of the resource module.
 */
export {
  route,
  Resource,
  ResourceEditor,
  ResourcePage,
  ResourceOverview,
  selectors,
  declareResource
}
