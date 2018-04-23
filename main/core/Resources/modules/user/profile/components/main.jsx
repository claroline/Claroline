import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/core/router'

import {UserPageContainer} from '#/main/core/user/containers/page'
import {User as UserTypes} from '#/main/core/user/prop-types'

import {select} from '#/main/core/data/details/selectors'
import {ProfileEdit} from '#/main/core/user/profile/editor/components/main'
import {ProfileShow} from '#/main/core/user/profile/player/components/main'

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
          component: ProfileEdit
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
  ).isRequired
}

const Profile = connect(
  state => ({
    user: select.data(select.details(state, 'user'))
  }),
  null
)(ProfileComponent)

export {
  Profile
}
