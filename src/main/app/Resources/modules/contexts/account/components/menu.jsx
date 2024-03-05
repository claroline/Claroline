import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'

import {ContextMenu} from '#/main/app/context/containers/menu'

const AccountMenu = (props) =>
  <ContextMenu
    basePath={props.basePath}
    title={trans('my_account')}
    tools={props.tools}
    shortcuts={props.shortcuts}
  />

AccountMenu.propTypes = {
  basePath: T.string,
  shortcuts: T.arrayOf(T.shape({
    type: T.oneOf(['tool', 'action']).isRequired,
    name: T.string.isRequired
  })),
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    permissions: T.object
  }))
}

export {
  AccountMenu
}
