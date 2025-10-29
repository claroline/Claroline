import {declareAction} from '#/main/app/action'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'

import {MODAL_AGENDA_EVENT_FORM} from '#/plugin/agenda/events/event/modals/form'

export default declareAction((events, refresher) => ({
  name: 'edit',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-pencil',
  label: trans('edit', {}, 'actions'),
  modal: [MODAL_AGENDA_EVENT_FORM, {
    event: events[0],
    onSave: refresher.update
  }],
  scope: ['object'],
  group: trans('management'),
  displayed: hasPermission('edit', events[0])
}))
