import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

export default (resourceNodes, nodesRefresher, path, currentUser) => ({
  name: 'notes',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-sticky-note',
  label: trans('show-notes', {}, 'actions'),
  displayed: !!currentUser,
  modal: []
})
