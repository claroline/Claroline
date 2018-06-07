import {trans} from '#/main/core/translation'

const action = () => ({
  name: 'move',
  type: 'modal',
  icon: 'fa fa-fw fa-arrows',
  label: trans('move', {}, 'actions'),
  modal: []
})

export {
  action
}
