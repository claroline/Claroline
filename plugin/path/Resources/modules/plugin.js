/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('InnovaPathBundle', {
  resources: {
    'innova_path': () => { return import(/* webpackChunkName: "plugin-path-path-resource" */ '#/plugin/path/resources/path') }
  }
})
