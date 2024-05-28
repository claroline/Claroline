import {withReducer} from '#/main/app/store/components/withReducer'

import {VideoResource as VideoResourceComponent} from '#/integration/peertube/resources/video/components/resource'
import {selectors, reducer} from '#/integration/peertube/resources/video/store'

const VideoResource = withReducer(selectors.STORE_NAME, reducer)(
  VideoResourceComponent
)

export {
  VideoResource
}
