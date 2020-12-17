import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineBigBlueButtonBundle', {
  resources: {
    'claroline_big_blue_button': () => { return import(/* webpackChunkName: "plugin-big-blue-button-bbb-resource" */ '#/integration/big-blue-button/resources/bbb') }
  },
  integration: {
    'bbb_config' : () => { return import(/* webpackChunkName: "plugin-bbb" */ '#/integration/big-blue-button/integration/bbb')}
  }
})
