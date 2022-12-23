import {reducer} from '#/integration/peertube/resources/video/store'
import {VideoCreation} from '#/integration/peertube/resources/video/components/creation'
import {VideoResource} from '#/integration/peertube/resources/video/containers/resource'
import {VideoMenu} from '#/integration/peertube/resources/video/components/menu'


/**
 * PeerTube video creation application.
 */
export const Creation = () => ({
  component: VideoCreation
})

/**
 * PeerTube video resource application.
 */
export default {
  component: VideoResource,
  menu: VideoMenu,
  store: reducer
}
