import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

// TODO : implement

export default () => ({
  name: 'share',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-share-alt',
  label: trans('share', {}, 'actions'),
  modal: []
})
