import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'
import {PostCard} from '#/plugin/blog/resources/blog/post/components/post'
import {hasPermission} from '#/main/core/resource/permissions'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {selectors} from '#/plugin/blog/resources/blog/store'

const PostsList = props =>
  <div className={'posts-list'}>
    <ListData
      name={selectors.STORE_NAME + '.posts'}
      fetch={{
        url: ['apiv2_blog_post_list', {blogId: props.blogId}],
        autoload: true
      }}
      open={(row) => ({
        type: LINK_BUTTON,
        target: `/${row.slug}`
      })}
      definition={[
        {
          name: 'title',
          label: trans('title', {}, 'platform'),
          type: 'string',
          primary: true,
          displayed: true
        },{
          name: 'publicationDate',
          label: trans('icap_blog_post_form_publicationDate', {}, 'icap_blog'),
          type: 'date',
          displayed: true
        },{
          name: 'fromDate',
          label: trans('icap_blog_post_form_publicationDateFrom', {}, 'icap_blog'),
          type: 'date',
          sortable: false
        },{
          name: 'toDate',
          label: trans('icap_blog_post_form_publicationDateTo', {}, 'icap_blog'),
          type: 'date',
          sortable: false
        },{
          name: 'content',
          label: trans('content', {}, 'platform'),
          type: 'string',
          sortable: false,
          displayed: false
        },{
          name: 'authorName',
          label: trans('author', {}, 'platform'),
          type: 'string'
        },{
          name: 'tags',
          label: trans('tags', {}, 'platform'),
          type: 'string',
          sortable: false,
          displayed: false
        }
      ]}

      selection={{}}
      card={PostCard}

      display={{
        available : [listConst.DISPLAY_LIST],
        current: listConst.DISPLAY_LIST
      }}
    />
  </div>

PostsList.propTypes ={
  goUp: T.func.isRequired,
  blogId: T.string.isRequired,
  posts: T.array,
  canEdit: T.bool,
  canPost: T.bool
}

const PostsContainer = connect(
  state => ({
    posts: selectors.posts(state).data,
    blogId: selectors.blog(state).data.id,
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
    canPost: hasPermission('post', resourceSelect.resourceNode(state))
  })
)(PostsList)

export {PostsContainer as Posts}
