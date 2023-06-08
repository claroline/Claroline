import {reducer} from '#/integration/youtube/resources/video/store'
import {VideoCreation} from '#/integration/youtube/resources/video/components/creation'
import {VideoResource} from '#/integration/youtube/resources/video/containers/resource'
import {VideoMenu} from '#/integration/youtube/resources/video/components/menu'


/**
 * youtube video creation application.
 */
export const Creation = () => ({
  component: VideoCreation
})

/**
 * youtube video resource application.
 */
export default {
  component: VideoResource,
  menu: VideoMenu,
  store: reducer,
  styles: [
    'claroline-distribution-integration-youtube-youtube'
  ]
}
