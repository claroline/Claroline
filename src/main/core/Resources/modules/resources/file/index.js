import {reducer} from '#/main/core/resources/file/store'
import {FileCreation} from '#/main/core/resources/file/containers/creation'
import {FileResource} from '#/main/core/resources/file/containers/resource'
import {FileMenu} from '#/main/core/resources/file/components/menu'

/**
 * File creation application.
 */
export const Creation = () => ({
  component: FileCreation
})

/**
 * File resource application.
 */
export default {
  component: FileResource,
  menu: FileMenu,
  store: reducer
}
