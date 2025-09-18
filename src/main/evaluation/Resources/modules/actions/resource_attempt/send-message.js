import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_MESSAGE} from '#/plugin/message/modals/message'
import {declareAction} from '#/main/app/action'

export default declareAction((attempts, refresher, path, currentUser) => ({
  name: 'send-message',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-envelope',
  label: trans('send-message', {}, 'actions'),
  displayed: -1 !== attempts.findIndex(attempt => get(attempt, 'user.id') !== get(currentUser, 'id')),
  modal: [MODAL_MESSAGE, {
    receivers: {
      users: attempts.map((row => row.user))
    }
  }],
  scope: ['object', 'collection'],
  group: trans('community')
}))
