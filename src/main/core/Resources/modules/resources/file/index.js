
import {declareResource} from '#/main/core/resource'
import {FileCreation} from '#/main/core/resources/file/containers/creation'
import {FileResource} from '#/main/core/resources/file/containers/resource'

/**
 * File creation application.
 */
export const Creation = () => ({
  component: FileCreation
})

/**
 * File resource application.
 */
export default declareResource(FileResource)
