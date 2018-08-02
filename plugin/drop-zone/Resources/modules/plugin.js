/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Dropzone plugin.
 */
registry.add('drop-zone', {
  resources: {
    'claroline_dropzone': () => { return import(/* webpackChunkName: "plugin-drop-zone-dropzone-resource" */ '#/plugin/drop-zone/resources/dropzone') }
  },
  data: {
    types: {
      'criteria': () => { return import(/* webpackChunkName: "dropzone-data-criteria" */     '#/plugin/drop-zone/data/criteria') }
    }
  }
})
