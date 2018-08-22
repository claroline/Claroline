import {trans} from '#/main/core/translation'

import {PostCard} from '#/plugin/blog/data/components/post-card'

export default {
  name: 'blog_posts',
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
        name: 'publicationDate',
        type: 'date',
        label: trans('date'),
        displayed: true
      }, {
        name: 'authorName',
        type: 'string',
        label: trans('author'),
        displayed: true
      }
    ],
    card: PostCard
  }
}
