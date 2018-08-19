import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import {User} from '#/plugin/blog/resources/blog/moderation/components/user.jsx'
import {select} from '#/plugin/blog/resources/blog/selectors'

const TrustedUsersComponent = (props) =>
{props.trustedUsers.map((user) =>
  <User key={user.id} user={user} />
)}

TrustedUsersComponent.propTypes = {
  blogId: T.string.isRequired
}

const TrustedUsers = connect(
  state => ({
    blogId: select.blog(state).data.id,
    trustedUsers: select.trustedUsers(state)
  })
)(TrustedUsersComponent)

export {TrustedUsers}
