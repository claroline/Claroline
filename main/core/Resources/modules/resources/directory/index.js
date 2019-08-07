
import {DirectoryResource} from '#/main/core/resources/directory/components/resource'
import {DirectoryMenu} from '#/main/core/resources/directory/containers/menu'
import {reducer} from '#/main/core/resources/directory/store'

/**
 * Directory resource application.
 */
export default {
  component: DirectoryResource,
  menu: DirectoryMenu,
  store: reducer
}
