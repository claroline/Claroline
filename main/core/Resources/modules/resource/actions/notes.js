import {trans} from '#/main/core/translation'
import {isAuthenticated} from '#/main/core/user/current'

const action = () => ({
  name: 'notes',
  type: 'modal',
  icon: 'fa fa-fw fa-sticky-note',
  label: trans('show-notes', {}, 'actions'),
  displayed: isAuthenticated(),
  modal: []
})

export {
  action
}
