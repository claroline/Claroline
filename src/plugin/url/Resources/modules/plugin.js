/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('HeVinciUrlBundle', {
  /**
   * Provides tab types for Home tools.
   */
  home: {
    'url': () => { return import(/* webpackChunkName: "url-home-url" */ '#/plugin/url/home/url') }
  },

  resources: {
    'hevinci_url': () => { return import(/* webpackChunkName: "plugin-url-url-resource" */ '#/plugin/url/resources/url') }
  }
})
