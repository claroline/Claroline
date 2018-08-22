/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('blog', {
  actions: {
    'blog_post' : () => { return import(/* webpackChunkName: "plugin-blog-action-post" */ '#/plugin/blog/resources/blog/actions/post') }
  },

  resources: {
    'icap_blog': () => { return import(/* webpackChunkName: "plugin-blog-blog-resource" */ '#/plugin/blog/resources/blog') }
  },

  data: {
    sources: {
      'blog_posts' : () => { return import(/* webpackChunkName: "blog-data-blog-posts" */  '#/plugin/blog/data/sources/blog-posts') }
    }
  }
})
