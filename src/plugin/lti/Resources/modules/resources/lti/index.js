import {reducer} from '#/plugin/lti/resources/lti/store'
import {LtiResource} from '#/plugin/lti/resources/lti/containers/resource'

/**
 * LTI resource application.
 */
export default {
  component: LtiResource,
  store: reducer
}