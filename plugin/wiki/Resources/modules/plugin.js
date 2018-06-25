import {registry} from '#/main/app/plugins/registry'

registry.add('wiki', {
  resources: {
    'icap_wiki': () => { return import(/* webpackChunkName: "plugin-forum-forum-resource" */ '#/plugin/wiki/resources/wiki') }
  }
})