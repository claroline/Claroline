import {ClacoFormResource} from '#/plugin/claco-form/resources/claco-form/containers/resource'
import {ClacoFormMenu} from '#/plugin/claco-form/resources/claco-form/containers/menu'
import {reducer} from '#/plugin/claco-form/resources/claco-form/store'

/**
 * ClacoForm resource application.
 */
export default {
  component: ClacoFormResource,
  menu: ClacoFormMenu,
  store: reducer,
  styles: ['claroline-distribution-plugin-claco-form-resource']
}
