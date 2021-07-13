import {reducer} from '#/main/core/tools/locations/store'
import {LocationsMenu} from '#/main/core/tools/locations/components/menu'
import {LocationsTool} from '#/main/core/tools/locations/containers/tool'

export default {
  component: LocationsTool,
  menu: LocationsMenu,
  store: reducer
}
