import {reducer} from '#/plugin/scorm/resources/scorm/store'
import {ScormCreation} from '#/plugin/scorm/resources/scorm/components/creation'
import {ScormResource} from '#/plugin/scorm/resources/scorm/containers/resource'
import {ScormMenu} from '#/plugin/scorm/resources/scorm/components/menu'

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
  menu: ScormMenu,
  store: reducer
}
