import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import {trans} from '#/main/core/translation'
import {constants as listConst} from '#/main/core/data/list/constants'
import {CommentModerationCard} from '#/plugin/blog/resources/blog/comment/components/comment-moderation.jsx'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'

const UnpublishedCommentsComponent = (props) =>
  <DataListContainer
    name="moderationComments"
    fetch={{
      url: ['apiv2_blog_comment_unpublished', {blogId: props.blogId}],
      autoload: true
    }}
    open={{
      action: (row) => `#/${row.slug}`
    }}
    definition={[
      {
        name: 'creationDate',
        label: trans('icap_blog_post_form_creationDate', {}, 'icap_blog'),
        type: 'date',
        displayed: true
      },{
        name: 'message',
        label: trans('content', {}, 'platform'),
        type: 'string',
        sortable: false,
        displayed: false
      },{
        name: 'authorName',
        label: trans('author', {}, 'platform'),
        type: 'string'
      }
    ]}
    selection={{}}
    card={CommentModerationCard}
    display={{
      available : [listConst.DISPLAY_LIST],
      current: listConst.DISPLAY_LIST
    }}
  />

UnpublishedCommentsComponent.propTypes = {
  blogId: T.string.isRequired
}

const UnpublishedComments = connect(
  state => ({
    blogId: state.blog.data.id
  })
)(UnpublishedCommentsComponent)

export {UnpublishedComments}