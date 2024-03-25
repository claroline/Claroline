import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {PageFull} from '#/main/app/page/components/full'
import {showBreadcrumb} from '#/main/app/layout/utils'

import {User as UserTypes} from '#/main/community/prop-types'
import {UserAvatar} from '#/main/app/user/components/avatar'

const AccountPage = (props) =>
  <PageFull
    {...omit(props, 'currentUser')}
    className="user-page"
    /*showBreadcrumb={showBreadcrumb()}*/
    path={[
      {
        type: LINK_BUTTON,
        label: trans('account', {}, 'context'),
        target: '/account'
      }
    ].concat(props.path || [])}
    title={props.currentUser.name}
    subtitle={props.title || props.currentUser.name}
    poster={props.currentUser.poster}
    icon={
      <UserAvatar user={props.currentUser} size="xl" />
    }
    meta={{
      title: `${trans('my_account')} - ${props.title || props.currentUser.username}`,
      description: get(props.currentUser, 'meta.description')
    }}
  >
    {props.children}
  </PageFull>

AccountPage.propTypes = {
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired,
  path: T.array,
  title: T.string,
  children: T.node.isRequired
}

export {
  AccountPage
}
