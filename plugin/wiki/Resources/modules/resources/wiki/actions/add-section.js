import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

export default () => ({
  name: 'add-section',
  type: MODAL_BUTTON,
  label: trans('add-section', {}, 'actions'),
  icon: 'fa fa-fw fa-plus',
  primary: true,
  modal: [

  ]
})
