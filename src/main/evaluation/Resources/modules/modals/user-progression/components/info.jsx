import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {LinkButton} from '#/main/app/buttons'
import {UserAvatar} from '#/main/app/user/components/avatar'

const UserProgressionInfo = ({user, title}) =>
  <div className={classes('d-flex flex-row gap-3 align-items-center')} role="presentation">
    <UserAvatar
      user={user}
      size="sm"
    />
    <div className="d-flex flex-column" role="presentation">
      <LinkButton className="fw-normal text-reset fs-5" target="#">{get(user, 'name') || trans('unknown')}</LinkButton>
      <span className="text-body-secondary">
        {title}
      </span>
    </div>
  </div>

UserProgressionInfo.propTypes = {
  user: T.object,
  title: T.string.isRequired
}

export {
  UserProgressionInfo
}
