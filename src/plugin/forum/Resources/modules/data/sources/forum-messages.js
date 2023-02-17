import {URL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {route} from '#/main/core/resource/routing'

import {MessageCard} from '#/plugin/forum/resources/forum/data/components/message-card'

export default {
  name: 'forum_messages',
  parameters: {
    primaryAction: (message) => ({
      type: URL_BUTTON,
      target: `#${route(message.meta.resource)}/subjects/show/${message.subject.id}`
    }),
    definition: [
      {
        name: 'content',
        type: 'html',
        label: trans('content'),
        displayed: true,
        primary: true
      }, {
        name: 'subject.title',
        type: 'string',
        label: trans('subject', {}, 'forum'),
        displayed: true
      }, {
        name: 'meta.created',
        alias: 'creationDate',
        type: 'date',
        label: trans('date'),
        displayed: true,
        options: {time: true}
      }, {
        name: 'meta.creator',
        type: 'user',
        label: trans('creator'),
        displayed: true
      }
    ],
    card: MessageCard
  }
}
