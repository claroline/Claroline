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
 *
 * @constructor
 */
export const App = () => ({
  component: ScormResource
})