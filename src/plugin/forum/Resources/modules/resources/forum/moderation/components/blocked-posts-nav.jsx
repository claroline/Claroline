import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {NavLink} from '#/main/app/router'

const BlockedPostsNav = (props) =>
  <div>
    <nav className="lateral-nav">
      <NavLink
        to={`${props.path}/moderation/blocked/subjects`}
        className="lateral-link"
      >
        {trans('blocked_subjects', {}, 'forum')}
      </NavLink>

      <NavLink
        to={`${props.path}/moderation/blocked/messages`}
        className="lateral-link"
      >
        {trans('blocked_messages', {}, 'forum')}
      </NavLink>
    </nav>
  </div>

BlockedPostsNav.propTypes = {
  path: T.string.isRequired
}

export {
  BlockedPostsNav
}