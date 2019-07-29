/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Favourite plugin.
 */
registry.add('HeVinciFavouriteBundle', {
  /**
   * Provides menu which can be used in the main header menu.
   */
  header: {
    'favourites': () => { return import(/* webpackChunkName: "plugin-favourite-header-favourites" */ '#/plugin/favourite/header/favourites') }
  },

  /**
   * Provides actions for base Claroline objects.
   */
  actions: {
    resource: {
      'favourite': () => { return import(/* webpackChunkName: "favourite-action-favourite-res" */ '#/plugin/favourite/resource/actions/favourite') }
    },

    workspace: {
      'favourite': () => { return import(/* webpackChunkName: "favourite-action-favourite-ws" */ '#/plugin/favourite/workspace/actions/favourite') }
    }
  }
})
