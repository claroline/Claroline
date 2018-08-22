import {trans} from '#/main/core/translation'

import {AnnouncementCard} from '#/plugin/announcement/data/components/announcement-card'

export default {
  name: 'announcements',
  parameters: {
    definition: [
      {
        name: 'title',
        type: 'string',
        label: trans('title'),
        displayed: true,
        primary: true
      }, {
        name: 'content',
        type: 'html',
        label: trans('content'),
        displayed: true
      }, {
        name: 'meta.publishedAt',
        alias: 'publicationDate',
        type: 'date',
        label: trans('date'),
        displayed: true
      }, {
        name: 'meta.author',
        alias: 'announcer',
        type: 'string',
        label: trans('author'),
        displayed: true
      }
    ],
    card: AnnouncementCard
  }
}
