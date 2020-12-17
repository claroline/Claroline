
import {UrlCreation} from '#/plugin/url/resources/url/containers/creation'
import {UrlResource} from '#/plugin/url/resources/url/containers/resource'
import {UrlMenu} from '#/plugin/url/resources/url/components/menu'
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
  menu: UrlMenu,
  store: reducer
}
