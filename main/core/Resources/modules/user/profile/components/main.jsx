import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes, withRouter} from '#/main/app/router'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {UserPageContainer} from '#/main/core/user/containers/page'
import {User as UserTypes} from '#/main/core/user/prop-types'

import {selectors} from '#/main/app/content/details/store'
import {select as profileSelect} from '#/main/core/user/profile/selectors'
import {ProfileEdit} from '#/main/core/user/profile/editor/components/main'
import {ProfileShow} from '#/main/core/user/profile/player/components/main'
import {ProfileBadgeList} from '#/plugin/open-badge/tools/badges/badge/components/profile-badges'

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
          disabled: !props.currentUser || (props.user.username !== props.currentUser.username &&
            props.currentUser.roles.filter(r => ['ROLE_ADMIN'].concat(props.parameters['roles_edition']).indexOf(r.name) > -1).length === 0
          )
        }, {
          path: '/badges/:id',
          component: ProfileBadgeList
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

const Profile = withRouter(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state),
      user: selectors.data(selectors.details(state, 'user')),
      parameters: profileSelect.parameters(state)
    })
  )(ProfileComponent)
)

export {
  Profile
}
