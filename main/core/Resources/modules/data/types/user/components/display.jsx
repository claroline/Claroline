import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

import {User as UserType} from '#/main/core/user/prop-types'
import {UserCard} from '#/main/core/user/components/card'

const UserDisplay = (props) => props.data ?
  <UserCard
    data={props.data}
    size="sm"
    orientation="col"
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
