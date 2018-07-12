/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Scorm plugin.
 */
registry.add('scorm', {
  resources: {
    'claroline_scorm': () => { return import(/* webpackChunkName: "plugin-scorm-resource" */ '#/plugin/scorm/resources/scorm') }
  }
})
