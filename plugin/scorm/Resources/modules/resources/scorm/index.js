import {reducer} from '#/plugin/scorm/resources/scorm/reducer'
import {ScormResource} from '#/plugin/scorm/resources/scorm/components/resource'
import {ScormCreation} from '#/plugin/scorm/resources/scorm/components/creation'

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
  component: ScormResource,
  store: reducer,
  styles: 'claroline-distribution-plugin-scorm-resource',
  initialData: initialData => Object.assign({}, {
    scorm: initialData.scorm,
    trackings: initialData.trackings,
    resource: {
      node: initialData.resourceNode,
      evaluation: initialData.evaluation
    }
  })
})