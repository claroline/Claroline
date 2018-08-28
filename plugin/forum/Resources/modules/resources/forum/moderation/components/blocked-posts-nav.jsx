import React from 'react'

import {trans} from '#/main/core/translation'
import {NavLink} from '#/main/app/router'

const BlockedPostsNav = () =>
  <div>
    <nav className="lateral-nav">
      <NavLink
        to='/moderation/blocked/subjects'
        className="lateral-link"
      >
        {trans('blocked_subjects', {}, 'forum')}
      </NavLink>

      <NavLink
        to='/moderation/blocked/messages'
        className="lateral-link"
      >
        {trans('blocked_messages', {}, 'forum')}
      </NavLink>
    </nav>
  </div>

export {
  BlockedPostsNav
}
