import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_OBJECT_TAGS} from '#/plugin/tag/modals/object-tags'

export default (resourceNodes, refresher) => ({
  name: 'tags',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-tags',
  label: trans('show-tags', {}, 'actions'),
  modal: [MODAL_OBJECT_TAGS, {
    objectClass: 'Claroline\\CoreBundle\\Entity\\Resource\\ResourceNode',
    objects: resourceNodes,
    update: (objects) => refresher.update(objects)
  }]
})
