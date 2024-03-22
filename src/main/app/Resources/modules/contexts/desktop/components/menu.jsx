import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {ContextMenu} from '#/main/app/context/containers/menu'

import {getActions} from '#/main/core/desktop'
import {User as UserTypes} from '#/main/community/user/prop-types'

const DesktopMenu = props =>
  <ContextMenu
    title={trans('desktop', {}, 'context')}
    tools={props.tools}
    actions={getActions(props.currentUser)}
  />

DesktopMenu.propTypes = {
  currentUser: T.shape(
    UserTypes.propTypes
  ),
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    permissions: T.object
  }))
}

export {
  DesktopMenu
}
