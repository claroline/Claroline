/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Project plugin.
 */
registry.add('ClarolineProjectBundle', {
  resources: {
    'claroline_project': () => { return import(/* webpackChunkName: "plugin-project-resource" */ '#/plugin/project/resources/project') }
  }
})
