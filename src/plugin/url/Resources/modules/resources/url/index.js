
import {declareResource} from '#/main/core/resource'

import {UrlCreation} from '#/plugin/url/resources/url/containers/creation'
import {UrlResource} from '#/plugin/url/resources/url/containers/resource'

export const Creation = () => ({
  component: UrlCreation
})

export default declareResource(UrlResource)
