import {MODAL_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/app/intl/translation'

export default () => ({
  name: 'copy',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-clone',
  label: trans('copy', {}, 'actions')
})
