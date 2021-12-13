import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {showBreadcrumb} from '#/main/app/layout/utils'

import {Profile} from '#/main/core/user/profile/containers/main'

const ProfileMain = (props) =>
  <Profile
    path="/account/profile"
    showBreadcrumb={showBreadcrumb()}
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('my_account'),
        target: '/account'
      }, {
        type: LINK_BUTTON,
        label: trans('user_profile'),
        target: '/account/profile'
      }
    ]}
    username={props.currentUser.username}
  />

ProfileMain.propTypes = {
  currentUser: T.shape({
    username: T.string.isRequired
  })
}

export {
  ProfileMain
}
