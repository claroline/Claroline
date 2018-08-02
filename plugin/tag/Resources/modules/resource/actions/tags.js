import {trans} from '#/main/core/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

const action = () => ({
  name: 'tags',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-tags',
  label: trans('edit-tags', {}, 'actions'),
  modal: []
})

export {
  action
}
