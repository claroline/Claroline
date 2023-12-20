import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_MESSAGE} from '#/plugin/message/modals/message'

export default (evaluations, refresher, path, currentUser) => ({
  name: 'send-message',
  type: MODAL_BUTTON,
  icon: 'fa fa-fw fa-envelope',
  label: trans('send-message', {}, 'actions'),
  displayed: -1 !== evaluations.findIndex(evaluation => get(evaluation, 'user.id') !== get(currentUser, 'id')),
  modal: [MODAL_MESSAGE, {
    receivers: {
      users: evaluations.map((row => row.user))
    }
  }],
  scope: ['object', 'collection']
})
