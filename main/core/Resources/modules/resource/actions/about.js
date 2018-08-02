import {trans} from '#/main/core/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_RESOURCE_ABOUT} from '#/main/core/resource/modals/about'

const action = (resourceNodes) => ({
  name: 'about',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-info',
  label: trans('show-info', {}, 'actions'),
  modal: [MODAL_RESOURCE_ABOUT, {
    resourceNode: resourceNodes[0]
  }]
})

export default (resourceNodes) => ({
  name: 'about',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-info',
  label: trans('show-info', {}, 'actions'),
  modal: [MODAL_RESOURCE_ABOUT, {
    resourceNode: resourceNodes[0]
  }]
})

export {
  action
}
