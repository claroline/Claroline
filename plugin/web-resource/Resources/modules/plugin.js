/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('web-resource', {
  resources: {
    'claroline_web_resource': () => { return import(/* webpackChunkName: "plugin-web-resource-web-resource-resource" */ '#/plugin/web-resource/resources/web-resource') }
  }
})
