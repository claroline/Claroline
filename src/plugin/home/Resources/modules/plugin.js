/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Home plugin.
 */
registry.add('ClarolineHomeBundle', {
  /**
   * Provides tab types for Home tools.
   */
  home: {
    'widgets': () => { return import(/* webpackChunkName: "home-home-widgets" */ '#/plugin/home/home/widgets') }
  },

  /**
   * Provides Desktop and/or Workspace tools.
   */
  tools: {
    'home': () => { return import(/* webpackChunkName: "home-tool-home" */ '#/plugin/home/tools/home') }
  },

  /**
   * Provides Administration tools.
   */
  administration: {
    'home': () => { return import(/* webpackChunkName: "home-tool-home" */ '#/plugin/home/tools/home') }
  },
})
