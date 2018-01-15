import React from 'react'
import {implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {Action as ActionTypes} from '#/main/core/layout/button/prop-types'

import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {TooltipLink} from '#/main/core/layout/button/components/tooltip-link.jsx'

// TODO : move in layout/action/components
// TODO : merge TooltipButton/TooltipLink with ActionTypes to avoid remap

const TooltipAction = props => React.createElement(
  typeof props.action === 'function' ? TooltipButton : TooltipLink,
  Object.assign({}, props, {
    title: props.label,
    [typeof props.action === 'function' ? 'onClick' : 'target']: props.action
  }),
  <span className={props.icon} />
)

implementPropTypes(TooltipAction, ActionTypes)

export {
  TooltipAction
}
