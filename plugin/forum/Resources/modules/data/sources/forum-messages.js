import {url} from '#/main/app/api'
import {URL_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/core/translation'

import {MessageCard} from '#/plugin/forum/resources/forum/data/components/message-card'

export default {
  name: 'forum_messages',
  parameters: {
    primaryAction: (message) => ({
      type: URL_BUTTON,
      target: url([ 'claro_resource_show_short', {
        id: message.meta.resource.id
      }]) + `#/subjects/show/${message.subject.id}`
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
