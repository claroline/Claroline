import {URL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {route} from '#/main/core/resource/routing'

import {PostCard} from '#/plugin/blog/data/components/post-card'

export default {
  name: 'blog_posts',
  parameters: {
    primaryAction: (post) => ({
      type: URL_BUTTON,
      target: `#${route(post.meta.resource)}/${post.slug}`
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
        name: 'publicationDate',
        type: 'date',
        label: trans('date'),
        displayed: true
      }, {
        name: 'meta.author',
        type: 'string',
        label: trans('author'),
        displayed: true
      }, {
        name: 'tags',
        type: 'tag',
        label: trans('tags'),
        displayable: false,
        sortable: false,
        options: {
          objectClass: 'Icap\\BlogBundle\\Entity\\Post'
        }
      }
    ],
    card: PostCard
  }
}
