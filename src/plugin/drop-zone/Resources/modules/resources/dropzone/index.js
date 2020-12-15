
import {DropzoneResource} from '#/plugin/drop-zone/resources/dropzone/containers/resource'
import {DropzoneMenu} from '#/plugin/drop-zone/resources/dropzone/components/menu'
import {reducer} from '#/plugin/drop-zone/resources/dropzone/store'

/**
 * Dropzone resource application.
 */
export default {
  component: DropzoneResource,
  menu: DropzoneMenu,
  store: reducer,
  styles: ['claroline-distribution-plugin-drop-zone-dropzone-resource']
}