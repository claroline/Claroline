import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContextMenu} from '#/main/app/context/containers/menu'

const PublicMenu = (props) =>
  <ContextMenu
    basePath={props.basePath}
    title={trans('home')}

    tools={props.tools}
    shortcuts={props.shortcuts}
    actions={[
      {
        name: 'login',
        type: LINK_BUTTON,
        label: trans('login', {}, 'actions'),
        target: '/login',
        displayed: !props.authenticated
      }, {
        name: 'create-account',
        type: LINK_BUTTON,
        label: trans('create-account', {}, 'actions'),
        target: '/registration',
        displayed: props.selfRegistration && !props.authenticated && !props.unavailable
      }
    ]}
  />

PublicMenu.propTypes = {
  basePath: T.string.isRequired,
  shortcuts: T.arrayOf(T.shape({
    type: T.oneOf(['tool', 'action']).isRequired,
    name: T.string.isRequired
  })),
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    permissions: T.object
  })),
  unavailable: T.bool.isRequired,
  selfRegistration: T.bool.isRequired,
  authenticated: T.bool.isRequired
}

export {
  PublicMenu
}
