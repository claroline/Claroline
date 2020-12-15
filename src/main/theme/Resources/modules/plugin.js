/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Theme plugin.
 */
registry.add('ClarolineThemeBundle', {
  /**
   * Provides current user Account sections.
   */
  /*account: {
    'appearance': () => { return import(/!* webpackChunkName: "theme-account-appearance" *!/ '#/main/theme/account/appearance') }
  },*/

  data: {
    types: {
      'color': () => { return import(/* webpackChunkName: "theme-data-type-color" */ '#/main/theme/data/types/color') },
      'icon' : () => { return import(/* webpackChunkName: "theme-data-type-icon" */  '#/main/theme/data/types/icon') }
    }
  }
})
