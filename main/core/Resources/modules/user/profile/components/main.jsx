import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/app/router'

import {UserPageContainer} from '#/main/core/user/containers/page'
import {User as UserTypes} from '#/main/core/user/prop-types'

import {currentUser} from '#/main/core/user/current'
import {select} from '#/main/core/data/details/selectors'
import {select as profileSelect} from '#/main/core/user/profile/selectors'
import {ProfileEdit} from '#/main/core/user/profile/editor/components/main'
import {ProfileShow} from '#/main/core/user/profile/player/components/main'

const authenticatedUser = currentUser()

const ProfileComponent = props =>
  <UserPageContainer
    user={props.user}
  >
    <Routes
      routes={[
        {
          path: '/show',
          component: ProfileShow
        }, {
          path: '/edit',
          component: ProfileEdit,
          disabled: props.user.username !== authenticatedUser.username &&
            authenticatedUser.roles.filter(r => ['ROLE_ADMIN'].concat(props.parameters['roles_edition']).indexOf(r.name) > -1).length === 0
        }
      ]}
      redirect={[
        {from: '/', exact: true, to: '/show'}
      ]}
    />
  </UserPageContainer>

ProfileComponent.propTypes = {
  user: T.shape(
    UserTypes.propTypes
  ).isRequired,
  parameters: T.object.isRequired
}

const Profile = connect(
  state => ({
    user: select.data(select.details(state, 'user')),
    parameters: profileSelect.parameters(state)
  }),
  null
)(ProfileComponent)

export {
  Profile
}
