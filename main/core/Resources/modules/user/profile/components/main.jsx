import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {UserPage} from '#/main/core/user/components/page'
import {User as UserTypes} from '#/main/core/user/prop-types'
import {ContentLoader} from '#/main/app/content/components/loader'
import {getToolBreadcrumb, showToolBreadcrumb} from '#/main/core/tool/utils'

import {ProfileEdit} from '#/main/core/user/profile/editor/components/main'
import {ProfileShow} from '#/main/core/user/profile/player/components/main'
import {ProfileBadgeList} from '#/plugin/open-badge/tools/badges/badge/components/profile-badges'

const ProfileComponent = props => {
  if (!props.loaded) {
    return (
      <ContentLoader
        size="lg"
        description="Nous chargeons votre utilisateur"
      />
    )
  }

  return(
    <UserPage
      showBreadcrumb={showToolBreadcrumb(props.currentContext.type, props.currentContext.data)}
      breadcrumb={getToolBreadcrumb('community', props.currentContext.type, props.currentContext.data)}
      user={props.user}
      path={props.path + '/' + props.user.publicUrl}
      currentUser={props.currentUser}
      updatePassword={props.updatePassword}
      updatePublicUrl={props.updatePublicUrl}
    >
      <Routes
        path={props.path + '/' + props.user.publicUrl}
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
          {from: '/', exact: true, to: '/show/main'}
        ]}
      />
    </UserPage>
  )
}

ProfileComponent.propTypes = {
  currentContext: T.object,
  user: T.shape(
    UserTypes.propTypes
  ).isRequired,
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired,
  path: T.string,
  loaded: T.bool,
  parameters: T.object.isRequired,
  updatePublicUrl: T.func.isRequired,
  updatePassword: T.func.isRequired
}

export {
  ProfileComponent
}
