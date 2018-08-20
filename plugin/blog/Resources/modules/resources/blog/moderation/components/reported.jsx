import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {constants as listConst} from '#/main/app/content/list/constants'
import {ListData} from '#/main/app/content/list/containers/data'
import {CommentModerationCard} from '#/plugin/blog/resources/blog/comment/components/comment-moderation'
import {selectors} from '#/plugin/blog/resources/blog/store'

const ReportedComponent = (props) =>
  <ListData
    name={selectors.STORE_NAME + '.reportedComments'}
    fetch={{
      url: ['apiv2_blog_comment_reported', {blogId: props.blogId}],
      autoload: true
    }}
    open={(row) => ({
      type: LINK_BUTTON,
      target: `/${row.slug}`
    })}
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
    card={CommentModerationCard}
    display={{
      available : [listConst.DISPLAY_LIST],
      current: listConst.DISPLAY_LIST
    }}
  />

ReportedComponent.propTypes = {
  blogId: T.string.isRequired
}

const Reported = connect(
  state => ({
    blogId: selectors.blog(state).data.id
  })
)(ReportedComponent)

export {Reported}
