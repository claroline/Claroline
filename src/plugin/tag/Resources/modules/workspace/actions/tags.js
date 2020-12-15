import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_OBJECT_TAGS} from '#/plugin/tag/modals/object-tags'

export default (workspaces, refresher) => ({
  name: 'tags',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-tags',
  label: trans('show-tags', {}, 'actions'),
  displayed: -1 !== workspaces.findIndex(workspace => hasPermission('administrate', workspace)),
  modal: [MODAL_OBJECT_TAGS, {
    objectClass: 'Claroline\\CoreBundle\\Entity\\Workspace\\Workspace',
    objects: workspaces.map(workspace => ({
      id: workspace.id,
      name: workspace.name
    })),
    update: (objects) => refresher.update(objects)
  }],
  scope: ['object', 'collection']
})
