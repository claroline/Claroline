import {trans} from '#/main/core/translation'

const action = () => ({
  name: 'copy',
  type: 'modal',
  icon: 'fa fa-fw fa-clone',
  label: trans('copy', {}, 'actions'),
  modal: []
})

export {
  action
}
