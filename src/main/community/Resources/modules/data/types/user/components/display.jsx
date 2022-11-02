import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {route} from '#/main/community/routing'
import {User as UserTypes} from '#/main/community/prop-types'
import {UserCard} from '#/main/core/user/components/card'

const UserDisplay = (props) => props.data ?
  <UserCard
    data={props.data}
    size="xs"
    primaryAction={{
      type: LINK_BUTTON,
      label: trans('open', {}, 'actions'),
      target: route(props.data)
    }}
  /> :
  <ContentPlaceholder
    icon="fa fa-user"
    title={trans('no_user')}
  />

UserDisplay.propTypes = {
  data: T.shape(
    UserTypes.propTypes
  )
}

export {
  UserDisplay
}
