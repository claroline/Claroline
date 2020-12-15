import {reducer} from '#/plugin/booking/tools/booking/store'
import {BookingTool} from '#/plugin/booking/tools/booking/containers/tool'
import {BookingMenu} from '#/plugin/booking/tools/booking/components/menu'

export default {
  component: BookingTool,
  menu: BookingMenu,
  store: reducer
}
