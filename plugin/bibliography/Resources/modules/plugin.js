/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('book-reference', {
  resources: {
    'icap_bibliography': () => { return import(/* webpackChunkName: "plugin-bibliography-book-reference-resource" */ '#/plugin/bibliography/resources/book-reference') }
  }
})
