import {reducer} from '#/integration/big-blue-button/resources/bbb/store'
import {BBBResource} from '#/integration/big-blue-button/resources/bbb/containers/resource'
import {BBBMenu} from '#/integration/big-blue-button/resources/bbb/components/menu'

/**
 * Big Blue Button resource application.
 */
export default {
  component: BBBResource,
  menu: BBBMenu,
  store: reducer
}
