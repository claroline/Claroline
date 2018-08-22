import {trans} from '#/main/core/translation'

import {MessageCard} from '#/plugin/forum/resources/forum/data/components/message-card'

export default {
  name: 'forum_messages',
  parameters: {
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
        displayed: true
      }, {
        name: 'meta.creator',
        alias: 'creator',
        type: 'string',
        label: trans('creator'),
        displayed: true,
        calculated: rowData => rowData.meta.creator.username
      }
    ],
    card: MessageCard
  }
}
