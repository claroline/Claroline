import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineProjectBundle', {
  resources: {
    'claroline_project': () => {
      return import(/* webpackChunkName: "plugin-project-resource" */ '#/plugin/project/resources/project')
    }
  }
})
