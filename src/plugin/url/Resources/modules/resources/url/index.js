
import {UrlCreation} from '#/plugin/url/resources/url/containers/creation'
import {UrlResource} from '#/plugin/url/resources/url/containers/resource'
import {reducer} from '#/plugin/url/resources/url/store'

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
  store: reducer
}
