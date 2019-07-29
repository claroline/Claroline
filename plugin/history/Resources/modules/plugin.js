/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the History plugin.
 */
registry.add('ClarolineHistoryBundle', {
  /**
   * Provides menu which can be used in the main header menu.
   */
  header: {
    'history': () => { return import(/* webpackChunkName: "history-header-history" */ '#/plugin/history/header/history') }
  }
})
