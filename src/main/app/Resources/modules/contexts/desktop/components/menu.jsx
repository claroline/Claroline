import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {ContextMenu} from '#/main/app/context/containers/menu'

const DesktopMenu = props =>
  <ContextMenu
    title={trans('desktop', {}, 'context')}
    tools={props.tools}
  />

DesktopMenu.propTypes = {
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    permissions: T.object
  }))
}

export {
  DesktopMenu
}
