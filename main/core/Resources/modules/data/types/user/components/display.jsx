import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {EmptyPlaceholder} from '#/main/app/content/components/placeholder'

import {route} from '#/main/core/user/routing'
import {User as UserType} from '#/main/core/user/prop-types'
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
  <EmptyPlaceholder
    icon="fa fa-user"
    title={trans('no_user')}
  />

UserDisplay.propTypes = {
  data: T.shape(UserType.propTypes)
}

export {
  UserDisplay
}
