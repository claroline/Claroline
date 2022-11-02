import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {route} from '#/main/community/routing'
import {User as UserType} from '#/main/community/prop-types'
import {UserCard} from '#/main/core/user/components/card'

const UsersDisplay = (props) => {
  if (!isEmpty(props.data)) {
    return (
      <Fragment>
        {props.data.map(user =>
          <UserCard
            key={`user-card-${user.id}`}
            data={user}
            size="xs"
            primaryAction={{
              type: LINK_BUTTON,
              label: trans('open', {}, 'actions'),
              target: route(user)
            }}
          />
        )}
      </Fragment>
    )
  }

  return (
    <ContentPlaceholder
      icon="fa fa-user"
      title={trans('no_user')}
    />
  )
}

UsersDisplay.propTypes = {
  data: T.arrayOf(T.shape(
    UserType.propTypes
  ))
}

export {
  UsersDisplay
}
