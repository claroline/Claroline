import {reducer} from '#/integration/big-blue-button/resources/bbb/store'
import {BBBResource} from '#/integration/big-blue-button/resources/bbb/containers/resource'

/**
 * Big Blue Button resource application.
 */
export default {
  component: BBBResource,
  store: reducer
}
