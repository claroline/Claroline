import {hasPermission} from '#/main/app/security'
import {trans} from '#/main/app/intl/translation'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_MESSAGE} from '#/plugin/message/modals/message'

export default (groups) => {
  const processable = groups.filter(group => hasPermission('edit', group))

  return {
    name: 'send-message',
    type: MODAL_BUTTON,
    icon: 'fa fa-fw fa-paper-plane',
    label: trans('send-message', {}, 'actions'),
    modal: [MODAL_MESSAGE, {
      receivers: {
        groups: processable
      }
    }],
    displayed: 0 !== processable.length,
    scope: ['object', 'collection'],
    group: trans('community')
  }
}
