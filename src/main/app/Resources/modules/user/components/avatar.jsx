import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {Thumbnail} from '#/main/app/components/thumbnail'
import {UserStatus} from '#/main/app/user/components/status'

/**
 * Avatar of a User.
 */
const UserAvatar = props =>
  <span className={classes('position-relative user-avatar', props.size && `user-avatar-${props.size}`, props.className)} role="presentation">
    <Thumbnail
      size={props.size}
      thumbnail={get(props.user, 'picture')}
      name={get(props.user, 'name') || trans('unknown')}
      square={true}
    >
      <span className="user-avatar-placeholder fa fa-user" />
    </Thumbnail>

    {get(props.user, 'status') && !props.noStatus &&
      <UserStatus
        className="position-absolute top-100 start-100 translate-middle"
        user={props.user}
        variant={classes({
          tooltip: !props.noStatusTooltip,
          bullet: props.noStatusTooltip
        })}
      />
    }
  </span>

UserAvatar.propTypes = {
  className: T.string,
  user: T.shape({
    picture: T.string,
    name: T.string.isRequired,
    status: T.string,
    lastActivity: T.string
  }),
  size: T.oneOf(['xs', 'sm', 'md', 'lg', 'xl']),
  noStatus: T.bool,
  noStatusTooltip: T.bool
}

UserAvatar.defaultProps = {
  size: 'md',
  noStatus: false,
  noStatusTooltip: false
}

export {
  UserAvatar
}
