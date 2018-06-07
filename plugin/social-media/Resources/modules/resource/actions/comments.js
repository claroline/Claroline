import {trans} from '#/main/core/translation'

const action = () => ({
  name: 'comments',
  type: 'modal',
  icon: 'fa fa-fw fa-comments',
  label: trans('show-comments', {}, 'actions'),
  modal: []
})

export {
  action
}
