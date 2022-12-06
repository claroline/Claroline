import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {AccountPage} from '#/main/app/account/containers/page'

import {route} from '#/main/app/account/routing'
import {ProfileShow} from '#/main/community/profile/containers/show'
import {ProfileEdit} from '#/main/community/profile/containers/edit'

import changePasswordAction from '#/main/community/actions/user/password-change'
import {selectors} from '#/main/community/account/profile/store'

const ProfileMain = (props) => {
  return (
    <AccountPage
      path={[
        {
          type: LINK_BUTTON,
          label: trans('user_profile'),
          target: route('user_profile')
        }
      ]}
      title={trans('user_profile')}
      toolbar="edit password-change | fullscreen more"
      actions={[
        {
          name: 'edit',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          target: '/account/profile/edit',
          primary: true
        },
        changePasswordAction([props.currentUser])
      ]}
    >
      <Routes
        path="/account/profile"
        routes={[
          {
            path: '/edit',
            disabled: !hasPermission('edit', props.currentUser),
            onEnter: () => props.reset(props.currentUser),
            render: () => (
              <ProfileEdit
                name={selectors.STORE_NAME}
                path="/account/profile/edit"
                user={props.currentUser}
                back="/account/profile"
              />
            )
          }, {
            path: '/',
            onEnter: () => props.reset(props.currentUser),
            render: () => (
              <ProfileShow
                path="/account/profile"
                name={selectors.STORE_NAME}
                user={props.currentUser}
              />
            )
          }
        ]}
      />
    </AccountPage>
  )
}

ProfileMain.propTypes = {
  currentUser: T.shape({
    username: T.string.isRequired
  }),
  reset: T.func.isRequired
}

export {
  ProfileMain
}
