import {trans} from '#/main/core/translation'

import {MODAL_RESOURCE_PARAMETERS} from '#/main/core/resource/modals/parameters'

const action = (resourceNodes, refreshNodes) => ({ // todo collection
  name: 'configure',
  type: 'modal',
  icon: 'fa fa-fw fa-cog',
  label: trans('configure', {}, 'actions'),
  modal: [MODAL_RESOURCE_PARAMETERS, {
    resourceNode: 1 === resourceNodes.length && resourceNodes[0],
    updateNode: (resourceNode) => refreshNodes([resourceNode])
  }]
})

export {
  action
}
