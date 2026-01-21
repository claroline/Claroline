import {URL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {route} from '#/main/core/resource/routing'

import {MessageCard} from '#/plugin/forum/data/components/message-card'

export default () => ({
  primaryAction: (message) => ({
    type: URL_BUTTON,
    target: `#${route(message.meta.resource)}/subjects/${message.subject.id}`
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
      alias: 'createdAt',
      type: 'date',
      label: trans('date'),
      displayed: true,
      options: {time: true}
    }, {
      name: 'meta.updated',
      alias: 'updatedAt',
      type: 'date',
      label: trans('last_modification'),
      displayed: false,
      options: {time: true}
    }, {
      name: 'meta.creator',
      type: 'user',
      alias: 'creator',
      label: trans('creator'),
      displayed: true
    }, {
      name: 'meta.resource',
      alias: 'resourceNode',
      type: 'resource',
      label: trans('resource')
    }, {
      name: 'meta.resource.tags',
      alias: 'resourceNode.tags',
      type: 'tag',
      label: trans('tags'),
      displayable: false,
      sortable: false
    }
  ],
  card: MessageCard
})
