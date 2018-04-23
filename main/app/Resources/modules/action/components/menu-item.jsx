import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Button} from '#/main/app/action/components/button'

const MenuItem = props =>
  <li role="presentation">
    <Button
      {...props}
      tabIndex={-1}
      onClick={props.onSelect ? (e) => props.onSelect(props.eventKey, e) : undefined}
    />
  </li>

implementPropTypes(MenuItem, ActionTypes, {
  // from standard dropdown MenuItem
  eventKey: T.string,
  onSelect: T.func
})

export {
  MenuItem
}
