
import {declareResource} from '#/main/core/resource'
import {VideoCreation} from '#/integration/youtube/resources/video/components/creation'
import {VideoResource} from '#/integration/youtube/resources/video/containers/resource'

/**
 * YouTube video creation application.
 */
export const Creation = () => ({
  component: VideoCreation
})

/**
 * YouTube video resource application.
 */
export default declareResource(VideoResource)
