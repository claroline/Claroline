import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {NavLink} from '#/main/app/router'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {Reported} from '#/plugin/blog/resources/blog/moderation/components/reported'
import {UnpublishedPosts} from '#/plugin/blog/resources/blog/moderation/components/unpublished-posts'
import {UnpublishedComments} from '#/plugin/blog/resources/blog/moderation/components/unpublished-comments'
import {ResourcePage} from '#/main/core/resource'

const ModerationComponent = (props) =>
  <ResourcePage
    title={trans('moderation', {}, 'icap_blog')}
  >
    <div className="row">
      <div className="col-md-3">
        <div>
          <nav className="lateral-nav">
            <NavLink
              to={`${props.path}/moderation/posts`}
              className="lateral-link"
            >
              {trans('unpublished-posts', {}, 'icap_blog')}
            </NavLink>
            <NavLink
              to={`${props.path}/moderation/comments/unpublished`}
              className="lateral-link"
            >
              {trans('unpublished-comments', {}, 'icap_blog')}
            </NavLink>
            <NavLink
              to={`${props.path}/moderation/comments/reported`}
              className="lateral-link"
            >
              {trans('reported-comments', {}, 'icap_blog')}
            </NavLink>
          </nav>
        </div>
      </div>
      <div className="col-md-9">
        <Routes
          path={props.path}
          routes={[
            {
              path: '/moderation/posts',
              component: UnpublishedPosts
            },
            {
              path: '/moderation/comments/unpublished',
              component: UnpublishedComments
            },
            {
              path: '/moderation/comments/reported',
              component: Reported
            }
          ]}
        />
      </div>
    </div>
  </ResourcePage>

ModerationComponent.propTypes = {
  path: T.string.isRequired
}

const Moderation = connect(
  (state) => ({
    path: resourceSelectors.path(state)
  })
)(ModerationComponent)

export {Moderation}