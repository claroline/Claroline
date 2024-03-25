
import {UrlCreation} from '#/plugin/url/resources/url/containers/creation'
import {UrlResource} from '#/plugin/url/resources/url/containers/resource'
import {reducer} from '#/plugin/url/resources/url/store'

export const Creation = () => ({
  component: UrlCreation
})

export default {
  component: UrlResource,
  store: reducer
}
