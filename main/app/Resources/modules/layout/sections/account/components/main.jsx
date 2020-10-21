import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {showBreadcrumb} from '#/main/app/layout/utils'

import {Profile} from '#/main/core/user/profile/containers/main'

const AccountMain = (props) =>
  <Routes
    path="/account"
    redirect={[
      {from: '/', exact: true, to: '/profile'}
    ]}
    routes={[
      {
        path: '/profile',
        render() {
          return (
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
                  target: '/profile'
                }
              ]}
              publicUrl={props.currentUser.publicUrl}
            />
          )
        }
      }
    ]}
  />

AccountMain.propTypes = {
  currentUser: T.shape({
    publicUrl: T.string.isRequired
  })
}

export {
  AccountMain
}
