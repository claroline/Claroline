
import {DropzoneResource} from '#/plugin/drop-zone/resources/dropzone/containers/resource'
import {reducer} from '#/plugin/drop-zone/resources/dropzone/store'

/**
 * Dropzone resource application.
 */
export default {
  component: DropzoneResource,
  store: reducer,
  styles: ['claroline-distribution-plugin-drop-zone-dropzone-resource']
}