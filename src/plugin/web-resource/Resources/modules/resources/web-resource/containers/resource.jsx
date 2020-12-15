import {withReducer} from '#/main/app/store/components/withReducer'

import {WebResource as WebResourceComponent} from '#/plugin/web-resource/resources/web-resource/components/resource'
import {selectors, reducer} from '#/plugin/web-resource/resources/web-resource/store'

const WebResource = withReducer(selectors.STORE_NAME, reducer)(WebResourceComponent)

export {
  WebResource
}
