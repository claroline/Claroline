import {trans} from '#/main/core/translation'

import {MODAL_RESOURCE_RIGHTS} from '#/main/core/resource/modals/rights'

const action = (resourceNodes) => ({ // todo collection
  name: 'rights',
  type: 'modal',
  icon: 'fa fa-fw fa-lock',
  label: trans('edit-rights', {}, 'actions'),
  modal: [MODAL_RESOURCE_RIGHTS, {
    resourceNode: 1 === resourceNodes.length && resourceNodes[0],
    bulk: 1 < resourceNodes.length
  }]
})

export {
  action
}
