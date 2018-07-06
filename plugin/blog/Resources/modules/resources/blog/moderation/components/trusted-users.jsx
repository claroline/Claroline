import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import {User} from '#/plugin/blog/resources/blog/moderation/components/user.jsx'

const TrustedUsersComponent = (props) =>
{props.trustedUsers.map((user) =>
  <User key={user.id} user={user} />
)}

TrustedUsersComponent.propTypes = {
  blogId: T.string.isRequired
}

const TrustedUsers = connect(
  state => ({
    blogId: state.blog.data.id,
    trustedUsers: state.trustedUsers
  })
)(TrustedUsersComponent)

export {TrustedUsers}