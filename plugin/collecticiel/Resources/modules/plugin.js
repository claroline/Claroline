/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Collecticiel plugin.
 */
registry.add('collecticiel', {
  resources: {
    'innova_collecticiel': () => { return import(/* webpackChunkName: "plugin-collecticiel-collecticiel-resource" */ '#/plugin/collecticiel/resources/collecticiel') }
  }
})
