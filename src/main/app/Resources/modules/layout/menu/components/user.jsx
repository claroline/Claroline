import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'

import {getPlatformRoles} from '#/main/community/utils'
import {User as UserTypes} from '#/main/community/prop-types'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {Button} from '#/main/app/action'
import {URL_BUTTON} from '#/main/app/buttons'
import {url} from '#/main/app/api'

const MenuUser = (props) =>
  <div className="app-menu-status">
    <UserAvatar className="user-avatar-md" picture={props.currentUser.picture} alt={false} />

    <div className="app-menu-status-info">
      <h3 className="h4">
        {props.currentUser.name}
      </h3>

      {getPlatformRoles(props.currentUser.roles).map(role => trans(role.translationKey)).join(', ')}
    </div>

    {false &&
      <div className="app-menu-status-toolbar">
        <Button
          className="btn-link"
          type={URL_BUTTON}
          label={trans('logout', {}, 'actions')}
          target={url(['claro_index'])}
        />
      </div>
    }
  </div>

MenuUser.propTypes = {
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired
}

MenuUser.propTypes = {
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired
}

export {
  MenuUser
}
