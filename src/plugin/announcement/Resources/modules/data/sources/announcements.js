import get from 'lodash/get'

import {URL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {route} from '#/main/app/context/routing'

import {AnnouncementCard} from '#/plugin/announcement/announcement/components/card'
import {declareDataSource} from '#/main/app/data/sources'

export default declareDataSource(() => ({
  primaryAction: (announcement) => ({
    type: URL_BUTTON,
    target: `#${route('workspace', get(announcement, 'workspace.slug'), 'announcement')}/${announcement.id}`
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
      name: 'meta.updatedAt',
      alias: 'updatedAt',
      type: 'date',
      label: trans('date'),
      displayed: true,
      options: {time: true}
    }, {
      name: 'meta.creator',
      type: 'string',
      label: trans('creator'),
      displayed: true
    }, {
      name: 'workspace',
      type: 'workspace',
      label: trans('workspace'),
      displayed: true,
      filterable: false,
      sortable: false
    }, {
      name: 'notDoneYet',
      type: 'boolean',
      label: trans('after_today'),
      displayed: false,
      displayable: false,
      filterable: true,
      sortable: false
    }
  ],
  card: AnnouncementCard
}))
