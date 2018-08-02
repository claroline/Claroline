import {trans} from '#/main/core/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {isAuthenticated} from '#/main/core/user/current'

const action = () => ({
  name: 'notes',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-sticky-note',
  label: trans('show-notes', {}, 'actions'),
  displayed: isAuthenticated(),
  modal: []
})

export {
  action
}
