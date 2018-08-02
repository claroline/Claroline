import {trans} from '#/main/core/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_RESOURCE_PARAMETERS} from '#/main/core/resource/modals/parameters'

const action = (resourceNodes, nodesRefresher) => ({ // todo collection
  name: 'configure',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-cog',
  label: trans('configure', {}, 'actions'),
  modal: [MODAL_RESOURCE_PARAMETERS, {
    resourceNode: 1 === resourceNodes.length && resourceNodes[0],
    updateNode: (resourceNode) => nodesRefresher.update([resourceNode])
  }]
})

export {
  action
}
