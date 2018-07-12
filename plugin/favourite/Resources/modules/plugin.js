/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Favourite plugin.
 */
registry.add('favourite', {
  actions: {
    'favourite': () => { return import(/* webpackChunkName: "favourite-action-favourite" */ '#/plugin/favourite/resource/actions/favourite') }
  }
})
