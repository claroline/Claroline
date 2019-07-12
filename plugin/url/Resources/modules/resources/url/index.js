import {reducer} from '#/plugin/url/resources/url/store'
import {UrlCreation} from '#/plugin/url/resources/url/containers/creation'
import {UrlResource} from '#/plugin/url/resources/url/containers/resource'

/**
 * @constructor
 */
export const Creation = () => ({
  component: UrlCreation
})

/**
 * @constructor
 */
export default {
  component: UrlResource,
  store: reducer,
  styles: ['claroline-distribution-main-core-iframe']
}
