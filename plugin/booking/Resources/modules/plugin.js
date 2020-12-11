import {registry} from '#/main/app/plugins/registry'

registry.add('booking', {
  tools: {
    'booking': () => { return import(/* webpackChunkName: "plugin-booking-tools-booking" */ '#/plugin/booking/tools/booking') }
  }
})
