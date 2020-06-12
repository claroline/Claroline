import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {hasPermission} from '#/main/app/security'
import {UserPage} from '#/main/core/user/components/page'
import {User as UserTypes} from '#/main/core/user/prop-types'
import {ContentLoader} from '#/main/app/content/components/loader'
import {getToolBreadcrumb, showToolBreadcrumb} from '#/main/core/tool/utils'

import {ProfileEdit} from '#/main/core/user/profile/editor/components/main'
import {ProfileShow} from '#/main/core/user/profile/player/components/main'

// TODO : remove hard dependency to plugin
import {Tracking} from '#/plugin/analytics/user/tracking/containers/main'

const ProfileComponent = props => {
  if (!props.loaded) {
    return (
      <ContentLoader
        size="lg"
        description="Nous chargeons votre utilisateur..."
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
      history={props.history}
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
            disabled: !hasPermission('edit', props.user)
          }, {
            path: '/dashboard',
            component: Tracking,
            disabled: !hasPermission('show_dashboard', props.user)
          }
        ]}
        redirect={[
          {from: '/', exact: true, to: '/show'}
        ]}
      />
    </UserPage>
  )
}

ProfileComponent.propTypes = {
  history: T.object.isRequired,
  currentContext: T.object,
  user: T.shape(
    UserTypes.propTypes
  ).isRequired,
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired,
  path: T.string,
  loaded: T.bool,
  parameters: T.object.isRequired
}

export {
  ProfileComponent
}
