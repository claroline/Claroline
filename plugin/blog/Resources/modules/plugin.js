/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('blog', {
  resources: {
    'icap_blog': () => { return import(/* webpackChunkName: "plugin-blog-blog-resource" */ '#/plugin/blog/resources/blog') }
  }
})
