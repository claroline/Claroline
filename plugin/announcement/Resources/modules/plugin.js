import {registry} from '#/main/app/plugins/registry'

registry.add('announcement', {
  resources: {
    'claroline_announcement_aggregate': () => { return import(/* webpackChunkName: "plugin-announcement-announcement-resource" */ '#/plugin/announcement/resources/announcement') }
  }
})
