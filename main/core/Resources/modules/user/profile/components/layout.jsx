import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const ProfileLayout = props =>
  <div className={classes('row user-profile', props.className)}>
    <div className="user-profile-aside col-md-3">
      {props.affix}
    </div>

    <div className="user-profile-content col-md-9">
      {props.content}
    </div>
  </div>

ProfileLayout.propTypes = {
  affix: T.node.isRequired,
  content: T.node.isRequired,
  className: T.string
}

export {
  ProfileLayout
}
