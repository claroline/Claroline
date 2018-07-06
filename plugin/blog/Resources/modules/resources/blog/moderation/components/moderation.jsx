import React from 'react'
import {connect} from 'react-redux'
import {Reported} from '#/plugin/blog/resources/blog/moderation/components/reported.jsx'
import {UnpublishedPosts} from '#/plugin/blog/resources/blog/moderation/components/unpublished-posts.jsx'
import {UnpublishedComments} from '#/plugin/blog/resources/blog/moderation/components/unpublished-comments.jsx'
import {Routes} from '#/main/app/router'
import {trans} from '#/main/core/translation'
import {NavLink} from '#/main/app/router'

const ModerationComponent = () =>
  <div>
    <h2>{trans('moderation', {}, 'icap_blog')}</h2>
    <div className="row">
      <div className="col-md-3">
        <div>
          <nav className="lateral-nav">
            <NavLink
              to='/moderation/posts'
              className="lateral-link"
            >
              {trans('unpublished-posts', {}, 'icap_blog')}
            </NavLink>
            <NavLink
              to='/moderation/comments/unpublished'
              className="lateral-link"
            >
              {trans('unpublished-comments', {}, 'icap_blog')}
            </NavLink>
            <NavLink
              to='/moderation/comments/reported'
              className="lateral-link"
            >
              {trans('reported-comments', {}, 'icap_blog')}
            </NavLink>
          </nav>
        </div>
      </div>
      <div className="col-md-9">
        <Routes
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
  </div>

ModerationComponent.propTypes = {
}

const Moderation = connect(
  () => ({
  })
)(ModerationComponent)

export {Moderation}