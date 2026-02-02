
import {declareDataSource} from '#/main/app/data/sources'
import {URL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {route} from '#/main/core/resource'

import {ChapterCard} from '#/plugin/lesson/chapter/components/card'

export default declareDataSource(() => ({
  primaryAction: (chapter) => ({
    type: URL_BUTTON,
    target: `#${route(chapter.resourceNode)}/${chapter.slug}`
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
      alias: 'text',
      type: 'html',
      label: trans('content'),
      displayed: true
    }, {
      name: 'meta.createdAt',
      alias: 'createdAt',
      type: 'date',
      label: trans('creation_date'),
      options: {time: true}
    }, {
      name: 'meta.updatedAt',
      alias: 'updatedAt',
      type: 'date',
      label: trans('last_modification'),
      options: {time: true}
    }, {
      name: 'meta.creator',
      type: 'string',
      label: trans('creator')
    }, {
      name: 'published',
      type: 'boolean',
      label: trans('published'),
      filterable: true,
      sortable: false
    }, {
      name: 'resourceNode',
      type: 'resource',
      label: trans('resource')
    }, {
      name: 'tags',
      type: 'tag',
      label: trans('tags')
    }, {
      name: 'resourceNode.tags',
      type: 'tag',
      label: trans('resource_tags', {}, 'resource')
    }
  ],
  card: ChapterCard
}))
