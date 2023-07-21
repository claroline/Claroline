import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Button} from '#/main/app/action/components/button'
import {MenuItem} from '#/main/app/overlays/menu'

const MenuAction = (props) =>
  <MenuItem
    as={Button}
    {...props}
  />

implementPropTypes(MenuAction, ActionTypes, {
  // from standard dropdown MenuItem
  eventKey: T.string,
  onSelect: T.func
})

export {
  MenuAction
}
