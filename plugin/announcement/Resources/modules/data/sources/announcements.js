import {URL_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/app/intl/translation'
import {route} from '#/main/core/resource/routing'

import {AnnouncementCard} from '#/plugin/announcement/data/components/announcement-card'

export default {
  name: 'announcements',
  parameters: {
    primaryAction: (announcement) => ({
      type: URL_BUTTON,
      target: `#${route(announcement.meta.resource)}/${announcement.id}`
    }),
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
      }, {
        name: 'meta.resource.workspace',
        type: 'workspace',
        label: trans('workspace'),
        displayed: true,
        filterable: false,
        sortable: false
      }, {
        name: 'notDoneYet',
        type: 'boolean',
        label: trans('not_done_yet'),
        displayed: false,
        displayable: false,
        filterable: true,
        sortable: false
      }
    ],
    card: AnnouncementCard
  }
}
