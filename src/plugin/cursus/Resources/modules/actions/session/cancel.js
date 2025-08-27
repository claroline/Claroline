import {declareAction} from '#/main/app/action'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {MODAL_SESSION_CANCEL} from '#/plugin/cursus/session/modals/cancel'
import get from 'lodash/get'

export default declareAction((sessions, refresher) => {
  const processable = sessions.filter(session => hasPermission('edit', session) && !get(session, 'meta.canceled', false))

  return {
    name: 'cancel',
    type: MODAL_BUTTON,
    icon: 'fa fa-fw fa-ban',
    label: trans('cancel', {}, 'actions'),
    displayed: 0 !== processable.length,
    group: trans('management'),
    scope: ['object', 'collection'],
    modal: [MODAL_SESSION_CANCEL, {
      sessions: processable,
      onCancel: refresher.update
    }],
    dangerous: true
  }
})
