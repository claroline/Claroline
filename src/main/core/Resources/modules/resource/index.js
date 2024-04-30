import {route} from '#/main/core/resource/routing'
import {ResourcePage} from '#/main/core/resource/components/page'
import {ResourceMain as Resource} from '#/main/core/resource/components/main'
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
  selectors,
  declareResource
}
