
const CORE_PLUGIN = 'core'

/**
 * Declares applications provided by the Core plugin.
 */
const coreConfiguration = {
  actions: [],
  resources: [],
  tools: [],
  widgets: {
    'list': () => { return import(/* webpackChunkName: "core-resource-list-widget" */ '#/main/core/widget/types/list') },

    'simple'       : () => { return import(/* webpackChunkName: "core-simple-widget" */ '#/main/core/widget/types/simple') },
    'resource-list': () => { return import(/* webpackChunkName: "core-resource-list-widget" */ '#/main/core/widget/types/resource-list') },
    'user-list'    : () => { return import(/* webpackChunkName: "core-user-list-preset" */ '#/main/core/widget/types/user-list') }
  }
}

export {
  CORE_PLUGIN,
  coreConfiguration
}
