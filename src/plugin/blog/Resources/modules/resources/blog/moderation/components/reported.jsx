import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {constants as listConst} from '#/main/app/content/list/constants'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {CommentModerationCard} from '#/plugin/blog/resources/blog/comment/components/comment-moderation'
import {selectors} from '#/plugin/blog/resources/blog/store'

const ReportedComponent = (props) =>
  <ListData
    name={selectors.STORE_NAME + '.reportedComments'}
    fetch={{
      url: ['apiv2_blog_comment_reported', {blogId: props.blogId}],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/${row.slug}`
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
    selectable={false}
  />

ReportedComponent.propTypes = {
  path: T.string.isRequired,
  blogId: T.string.isRequired
}

const Reported = connect(
  state => ({
    path: resourceSelectors.path(state),
    blogId: selectors.blog(state).data.id
  })
)(ReportedComponent)

export {Reported}
