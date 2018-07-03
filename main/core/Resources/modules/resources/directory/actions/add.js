import {trans} from '#/main/core/translation'
import {MODAL_RESOURCE_CREATION} from '#/main/core/resource/modals/creation'

const action = (resourceNodes) => ({
  name: 'add',
  type: 'modal',
  label: trans('add_resource', {}, 'resource'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  modal: [MODAL_RESOURCE_CREATION, {
    parent: resourceNodes[0]
  }]
})

export {
  action
}
