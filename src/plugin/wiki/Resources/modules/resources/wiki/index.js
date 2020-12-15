import {reducer} from '#/plugin/wiki/resources/wiki/store'
import {WikiResource} from '#/plugin/wiki/resources/wiki/containers/resource'

/**
 * Wiki resource application.
 */
export default {
  component: WikiResource,
  store: reducer,
  styles: ['claroline-distribution-plugin-wiki-wiki-resource']
}
