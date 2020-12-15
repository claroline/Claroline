import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import {User} from '#/plugin/blog/resources/blog/moderation/components/user.jsx'
import {selectors} from '#/plugin/blog/resources/blog/store'

const TrustedUsersComponent = (props) =>
{props.trustedUsers.map((user) =>
  <User key={user.id} user={user} />
)}

TrustedUsersComponent.propTypes = {
  blogId: T.string.isRequired
}

const TrustedUsers = connect(
  state => ({
    blogId: selectors.blog(state).data.id,
    trustedUsers: selectors.trustedUsers(state)
  })
)(TrustedUsersComponent)

export {TrustedUsers}
