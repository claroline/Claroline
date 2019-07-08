
import {PathResource} from '#/plugin/path/resources/path/containers/resource'
import {PathMenu} from '#/plugin/path/resources/path/containers/menu'
import {reducer} from '#/plugin/path/resources/path/store'

/**
 * Path resource application.
 */
export default {
  component: PathResource,
  menu: PathMenu,
  store: reducer,
  styles: ['claroline-distribution-plugin-path-path-resource']
}
