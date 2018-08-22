/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('hevinci_url', {
  resources: {
    hevinci_url: () => { return import(/* webpackChunkName: "plugin-url-url-resource" */ '#/plugin/url/resources/url') }
  }
})
