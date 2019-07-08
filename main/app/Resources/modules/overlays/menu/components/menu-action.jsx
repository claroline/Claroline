import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Button} from '#/main/app/action/components/button'

// todo : find a way to disallow `menu` actions

const MenuAction = props =>
  <li role="presentation">
    <Button
      {...props}
      tabIndex={-1}
      onClick={props.onSelect ? (e) => props.onSelect(props.eventKey, e) : undefined}
    />
  </li>

implementPropTypes(MenuAction, ActionTypes, {
  // from standard dropdown MenuItem
  eventKey: T.string,
  onSelect: T.func
})

export {
  MenuAction
}
