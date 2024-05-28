import {withReducer} from '#/main/app/store/reducer'

import {VideoResource as VideoResourceComponent} from '#/integration/youtube/resources/video/components/resource'
import {selectors, reducer} from '#/integration/youtube/resources/video/store'

const VideoResource = withReducer(selectors.STORE_NAME, reducer)(
  VideoResourceComponent
)

export {
  VideoResource
}
