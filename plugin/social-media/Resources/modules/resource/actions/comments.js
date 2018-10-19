import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

const action = () => ({
  name: 'comments',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-comments',
  label: trans('show-comments', {}, 'actions'),
  modal: []
})

export {
  action
}
