
import {declareResource} from '#/main/core/resource'
import {ScormCreation} from '#/plugin/scorm/resources/scorm/components/creation'
import {ScormResource} from '#/plugin/scorm/resources/scorm/containers/resource'

/**
 * Scorm creation app.
 */
export const Creation = () => ({
  component: ScormCreation
})

/**
 * Scorm resource application.
 */
export default declareResource(ScormResource)
