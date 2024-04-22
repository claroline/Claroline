
import {declareResource} from '#/main/core/resource'
import {WebResource} from '#/plugin/web-resource/resources/web-resource/containers/resource'
import {WebResourceCreation} from '#/plugin/web-resource/resources/web-resource/components/creation'

/**
 * WebResource creation app.
 */
export const Creation = () => ({
  component: WebResourceCreation
})

/**
 * WebResource application.
 */
export default declareResource(WebResource)
