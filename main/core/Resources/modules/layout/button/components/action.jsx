import React from 'react'

import {implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {Action as ActionTypes} from '#/main/core/layout/action/prop-types'

import {Button} from '#/main/core/layout/button/components/button.jsx'
import {Link} from '#/main/core/layout/button/components/link.jsx'

// TODO : move in layout/action/components
// TODO : merge Button/Link with ActionTypes to avoid remap

const Action = props => React.createElement(
  typeof props.action === 'function' ? Button : Link,
  Object.assign({}, props, {
    title: props.label,
    [typeof props.action === 'function' ? 'onClick' : 'target']: props.action
  }), [
    <span key="action-icon" className={props.icon} />,
    props.label
  ]
)

implementPropTypes(Action, ActionTypes)

export {
  Action
}
