import {reducer} from '#/plugin/scorm/resources/scorm/store'
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
export default {
  component: ScormResource,
  store: reducer,
  styles: ['claroline-distribution-plugin-scorm-resource']
}