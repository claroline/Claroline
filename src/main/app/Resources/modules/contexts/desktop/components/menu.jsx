import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {ContextMenu} from '#/main/app/context/containers/menu'

import {getActions} from '#/main/core/desktop'

const DesktopMenu = props =>
  <ContextMenu
    basePath={props.basePath}
    title={trans('desktop')}
    tools={props.tools}
    shortcuts={props.shortcuts}
    actions={getActions(props.currentUser)}
  />

DesktopMenu.propTypes = {
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

DesktopMenu.defaultProps = {
  shortcuts: [],
  tools: []
}

export {
  DesktopMenu
}
