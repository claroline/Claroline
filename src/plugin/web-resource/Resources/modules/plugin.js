/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineWebResourceBundle', {
  resources: {
    'claroline_web_resource': () => { return import(/* webpackChunkName: "plugin-web-resource-web-resource-resource" */ '#/plugin/web-resource/resources/web-resource') }
  }
})
