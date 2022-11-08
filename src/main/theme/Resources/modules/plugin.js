/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Theme plugin.
 */
registry.add('ClarolineThemeBundle', {
  data: {
    types: {
      'color': () => { return import(/* webpackChunkName: "theme-data-type-color" */ '#/main/theme/data/types/color') },
      'icon' : () => { return import(/* webpackChunkName: "theme-data-type-icon" */  '#/main/theme/data/types/icon') }
    }
  }
})
