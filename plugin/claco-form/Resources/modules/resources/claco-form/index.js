import {ClacoFormResource} from '#/plugin/claco-form/resources/claco-form/containers/resource'
import {reducer} from '#/plugin/claco-form/resources/claco-form/store'

/**
 * ClacoForm resource application.
 */
export default {
  component: ClacoFormResource,
  store: reducer,
  styles: ['claroline-distribution-plugin-claco-form-resource']
}
