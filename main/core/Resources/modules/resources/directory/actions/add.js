import {trans} from '#/main/core/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_RESOURCE_CREATION} from '#/main/core/resource/modals/creation'

const action = (resourceNodes, nodesRefresher) => ({
  name: 'add',
  type: MODAL_BUTTON,
  label: trans('add_resource', {}, 'resource'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  modal: [MODAL_RESOURCE_CREATION, {
    parent: resourceNodes[0],
    add: (newNode) => nodesRefresher.add([newNode])
  }]
})

export {
  action
}
