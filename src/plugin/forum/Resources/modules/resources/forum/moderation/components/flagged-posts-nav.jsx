import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {NavLink} from '#/main/app/router'

const FlaggedPostsNav = (props) =>
  <div>
    <nav className="lateral-nav">
      <NavLink
        to={`${props.path}/moderation/flagged/subjects`}
        className="lateral-link"
      >
        {trans('flagged_subjects', {}, 'forum')}
      </NavLink>

      <NavLink
        to={`${props.path}/moderation/flagged/messages`}
        className="lateral-link"
      >
        {trans('flagged_messages', {}, 'forum')}
      </NavLink>
    </nav>
  </div>

FlaggedPostsNav.propTypes = {
  path: T.string.isRequired
}

export {
  FlaggedPostsNav
}