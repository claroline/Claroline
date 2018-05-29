/* eslint-disable */

// TODO find a way to re enable ESLINT it crash because of the import

const CORE_PLUGIN = 'core'

/**
 * Declares applications provided by the Core plugin.
 */
const coreConfiguration = {
  /*actions: [
    {
      name: 'publish',
      type: 'async',
      icon: 'fa fa-fw fa-eye-slash',
      label: trans('publish'),
      group: 'management',
      request: {
        type: 'publish',
        url: ['claro_resource_node_publish', {id: resourceNode.id}],
        request: {method: 'PUT'}
      }
    }, {
      name: 'edit-properties',
      type: 'modal',
      icon: 'fa fa-fw fa-pencil',
      label: trans('edit-properties'),
      group: 'management',
      modal: [MODAL_RESOURCE_PROPERTIES]
    }, {
      name: 'edit-rights',
      type: 'modal',
      label: trans('edit-rights'),
      group: 'management',
      modal: [MODAL_RESOURCE_RIGHTS]
    }, {
      name: 'open-tracking',
      type: 'url',
      target: ['claro_resource_action', {
        resourceType: resourceNode.meta.type,
        action: 'open-tracking',
        node: resourceNode.autoId
      }]
    }
  ],*/
  resources: {
    'text': () => { return import(/* webpackChunkName: "core-text-resource" */ '#/main/core/resources/text') },
    // todo move me inside exo plugin
    'ujm_exercise': () => { return import(/* webpackChunkName: "plugin-exo-quiz-resource" */ '#/plugin/exo/resources/quiz') }
  },
  tools: [],
  widgets: {
    'list': () => { return import(/* webpackChunkName: "core-resource-list-widget" */ '#/main/core/widget/types/list') },

    'simple'       : () => { return import(/* webpackChunkName: "core-simple-widget" */ '#/main/core/widget/types/simple') },
    'resource-list': () => { return import(/* webpackChunkName: "core-resource-list-widget" */ '#/main/core/widget/types/resource-list') },
    'user-list'    : () => { return import(/* webpackChunkName: "core-user-list-preset" */ '#/main/core/widget/types/user-list') }
  }
}



/*const CorePlugin = new Plugin('core', {
  actions: [],
  resources: {
    'text': () => { return import(/!* webpackChunkName: "core-text-resource" *!/ '#/main/core/resources/text') }
  },
  tools: [],
  widgets: {
    'list': () => { return import(/!* webpackChunkName: "core-resource-list-widget" *!/ '#/main/core/widget/types/list') },

    'simple'       : () => { return import(/!* webpackChunkName: "core-simple-widget" *!/ '#/main/core/widget/types/simple') },
    'resource-list': () => { return import(/!* webpackChunkName: "core-resource-list-widget" *!/ '#/main/core/widget/types/resource-list') },
    'user-list'    : () => { return import(/!* webpackChunkName: "core-user-list-preset" *!/ '#/main/core/widget/types/user-list') }
  }
})*/

export {
  CORE_PLUGIN,
  coreConfiguration
}
