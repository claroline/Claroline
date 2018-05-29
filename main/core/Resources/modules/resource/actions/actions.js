import get from 'lodash/get'

import {trans} from '#/main/core/translation'
import {isAuthenticated} from '#/main/core/user/current'

import {MODAL_RESOURCE_ABOUT} from '#/main/core/resource/modals/about'
import {MODAL_RESOURCE_PARAMETERS} from '#/main/core/resource/modals/parameters'
import {MODAL_RESOURCE_IMPERSONATION} from '#/main/core/resource/modals/impersonation'
import {MODAL_RESOURCE_RIGHTS} from '#/main/core/resource/modals/rights'

// TODO : move in directory resource
/*const CreateAction = new ResourceAction('create', (resourceNodes, scope) => ({
  type: 'modal',
  label: trans('create', {}, 'actions'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  modal: [MODAL_RESOURCE_CREATE, {
    availableTypes: resourceNodes[0].permissions.create
  }]
}))*/

export const actions = {
  open: (resourceNodes) => ({
    type: 'url',
    label: trans('open', {}, 'actions'),
    default: true,
    icon: 'fa fa-fw fa-arrow-circle-o-right',
    target: ['claro_resource_open', {
      node: resourceNodes[0].autoId,
      resourceType: resourceNodes[0].meta.type
    }]
  }),

  about: (resourceNodes) => ({
    type: 'modal',
    icon: 'fa fa-fw fa-info',
    label: trans('show-info', {}, 'actions'),
    modal: [MODAL_RESOURCE_ABOUT, {
      resourceNode: resourceNodes[0]
    }]
  }),

  edit: (resourceNode) => ({
    type: 'link',
    icon: 'fa fa-fw fa-pencil',
    label: trans('edit', {}, 'actions'),
    primary: true,
    target: '/edit'
  }),

  configure: (resourceNodes) => ({ // todo collection
    type: 'modal',
    icon: 'fa fa-fw fa-cog',
    label: trans('configure', {}, 'actions'),
    modal: [MODAL_RESOURCE_PARAMETERS, {
      resourceNode: 1 === resourceNodes.length && resourceNodes[0],
      bulk: 1 < resourceNodes.length
    }]
  }),

  rights: (resourceNodes) => ({ // todo collection
    type: 'modal',
    icon: 'fa fa-fw fa-lock',
    label: trans('edit-rights', {}, 'actions'),
    modal: [MODAL_RESOURCE_RIGHTS, {
      resourceNode: 1 === resourceNodes.length && resourceNodes[0],
      bulk: 1 < resourceNodes.length
    }]
  }),

  publish: (resourceNodes) => ({ // todo collection
    type: 'async',
    icon: 'fa fa-fw fa-eye',
    label: trans('publish', {}, 'actions'),
    displayed: -1 !== resourceNodes.findIndex(node => !get(node, 'meta.published')),
    subscript: 1 === resourceNodes.length && {
      type: 'default',
      value: get(resourceNodes[0], 'meta.views')
    },
    request: {
      type: 'publish',
      url: ['claro_resource_action', {
        resourceType: resourceNodes[0].meta.type,
        action: 'publish',
        id: resourceNodes[0].id
      }],
      request: {
        method: 'PUT'
      }
    }
  }),

  unpublish: (resourceNodes) => ({ // todo collection
    type: 'async',
    icon: 'fa fa-fw fa-eye-slash',
    label: trans('unpublish', {}, 'actions'),
    displayed: -1 !== resourceNodes.findIndex(node => !!get(node, 'meta.published')),
    subscript: 1 === resourceNodes.length && {
      type: 'primary',
      value: get(resourceNodes[0], 'meta.views')
    },
    request: {
      type: 'unpublish',
      url: ['claro_resource_action', {
        resourceType: resourceNodes[0].meta.type,
        action: 'unpublish',
        id: resourceNodes[0].id
      }],
      request: {
        method: 'PUT'
      }
    }
  }),

  export: (resourceNodes) => ({ // todo collection
    type: 'async',
    icon: 'fa fa-fw fa-download',
    label: trans('export', {}, 'actions'),
    request: {
      url: ['claro_resource_action', {
        resourceType: resourceNodes[0].meta.type,
        action: 'export',
        id: resourceNodes[0].id
      }]
    }
  }),

  delete: (resourceNodes) => ({ // todo collection
    type: 'async',
    icon: 'fa fa-fw fa-trash-o',
    label: trans('delete', {}, 'actions'),
    dangerous: true,
    confirm: {
      title: trans('resources_delete_confirm'),
      message: trans('resources_delete_message')
    },
    request: {
      url: ['claro_resource_action', {
        resourceType: resourceNodes[0].meta.type,
        action: 'delete',
        id: resourceNodes[0].id
      }],
      request: {
        method: 'DELETE'
      }
    }
  }),

  logs: (resourceNodes) => ({
    type: 'callback', // TODO : it will be section
    icon: 'fa fa-fw fa-line-chart',
    label: trans('show-logs', {}, 'actions'),
    callback: () => true
  }),

  // todo move in notification bundle
  follow: (resourceNodes) => ({
    type: 'async',
    icon: 'fa fa-fw fa-bell-o',
    label: trans('follow', {}, 'actions'),
    displayed: isAuthenticated() && -1 !== resourceNodes.findIndex(node => !get(node, 'notifications.enabled')),
    request: {
      url: ['claro_resource_action', {  // todo collection
        resourceType: resourceNodes[0].meta.type,
        action: 'follow',
        id: resourceNodes[0].id
      }],
      request: {
        method: 'PUT'
      }
    }
  }),

  unfollow: (resourceNodes) => ({
    type: 'async',
    icon: 'fa fa-fw fa-bell-o-slash',
    label: trans('unfollow', {}, 'actions'),
    displayed: isAuthenticated() && -1 !== resourceNodes.findIndex(node => !!get(node, 'notifications.enabled')),
    request: {
      url: ['claro_resource_action', {  // todo collection
        resourceType: resourceNodes[0].meta.type,
        action: 'unfollow',
        id: resourceNodes[0].id
      }],
      request: {
        method: 'PUT'
      }
    }
  }),

  notifications: (resourceNodes) => ({
    type: 'modal',
    icon: 'fa fa-fw fa-bell',
    label: trans('show-notifications', {}, 'actions'),
    modal: []
  }),

  followers: (resourceNodes) => ({
    type: 'modal',
    icon: 'fa fa-fw fa-users',
    label: trans('show-followers', {}, 'actions'),
    modal: []
  })
}
