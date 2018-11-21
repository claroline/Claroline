import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {isAuthenticated} from '#/main/app/security'

export default () => ({
  name: 'notes',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-sticky-note',
  label: trans('show-notes', {}, 'actions'),
  displayed: isAuthenticated(),
  modal: []
})
