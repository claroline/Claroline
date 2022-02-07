/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('InnovaPathBundle', {
  resources: {
    'innova_path': () => { return import(/* webpackChunkName: "plugin-path-path-resource" */ '#/plugin/path/resources/path') }
  },

  analytics: {
    resource: {
      'path_progression': () => { return import(/* webpackChunkName: "plugin-path-analytics-resource-progression" */ '#/plugin/path/analytics/resource/progression') }
    },
    workspace: {
      'paths': () => { return import(/* webpackChunkName: "plugin-path-analytics-workspace-path" */ '#/plugin/path/analytics/workspace/path') }
    }
  }
})
