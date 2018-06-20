import React from 'react'
// import {PropTypes as T} from 'prop-types'
// import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {NavLink} from '#/main/app/router'


const FlaggedPostsNav = () =>
  <div>
    <nav className="lateral-nav">
      <NavLink
        to='/moderation/flagged/subjects'
        className="lateral-link"
      >
        {trans('flagged_subjects', {}, 'forum')}
      </NavLink>
      <NavLink
        to='/moderation/flagged/messages'
        className="lateral-link"
      >
        {trans('flagged_messages', {}, 'forum')}
      </NavLink>
    </nav>
  </div>




export {
  FlaggedPostsNav
}
