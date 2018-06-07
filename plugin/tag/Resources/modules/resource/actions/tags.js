import {trans} from '#/main/core/translation'

const action = () => ({
  name: 'tags',
  type: 'modal',
  icon: 'fa fa-fw fa-tags',
  label: trans('edit-tags', {}, 'actions'),
  modal: []
})

export {
  action
}
