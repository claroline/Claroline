
import {declareResource} from '#/main/core/resource'
import {VideoCreation} from '#/integration/peertube/resources/video/components/creation'
import {VideoResource} from '#/integration/peertube/resources/video/containers/resource'

/**
 * PeerTube video creation application.
 */
export const Creation = () => ({
  component: VideoCreation
})

/**
 * PeerTube video resource application.
 */
export default declareResource(VideoResource)
