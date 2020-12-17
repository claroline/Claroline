import {reducer} from '#/main/core/resources/text/store'
import {TextResource} from '#/main/core/resources/text/containers/resource'
import {TextMenu} from '#/main/core/resources/text/components/menu'

/**
 * Text resource application.
 */
export default {
  component: TextResource,
  menu: TextMenu,
  store: reducer
}
