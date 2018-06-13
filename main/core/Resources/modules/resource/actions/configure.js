import {trans} from '#/main/core/translation'

import {MODAL_RESOURCE_PARAMETERS} from '#/main/core/resource/modals/parameters'

const action = (resourceNodes) => ({ // todo collection
  name: 'configure',
  type: 'modal',
  icon: 'fa fa-fw fa-cog',
  label: trans('configure', {}, 'actions'),
  modal: [MODAL_RESOURCE_PARAMETERS, {
    resourceNode: 1 === resourceNodes.length && resourceNodes[0]
  }]
})

export {
  action
}
