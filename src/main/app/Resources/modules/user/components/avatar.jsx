import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {asset} from '#/main/app/config/asset'
import {UserStatus} from '#/main/app/user/components/status'
import {ThumbnailIcon} from '#/main/app/components/thumbnail-icon'
import {trans} from '#/main/app/intl'

/**
 * Avatar of a User.
 */
const UserAvatar = props =>
  <span className={classes('position-relative user-avatar', props.size && `user-avatar-${props.size}`, props.className)} role="presentation">
    <ThumbnailIcon
      size={props.size}
      thumbnail={get(props.user, 'picture')}
      name={get(props.user, 'name') || trans('unknown')}
    >
      <span className="user-avatar-placeholder fa fa-user"/>
    </ThumbnailIcon>
    {/*{get(props.user, 'picture') ?
      <img src={asset(get(props.user, 'picture'))} alt="avatar" /> :
      <span className="user-avatar-placeholder avatar fa fa-user"/>
    }*/}

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
