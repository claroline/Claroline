import React from 'react'
//import {PropTypes as T} from 'prop-types'

import {Action as ActionTypes} from '#/main/core/layout/button/prop-types'

import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {TooltipLink} from '#/main/core/layout/button/components/tooltip-link.jsx'

// TODO : merge TooltipButton/TooltipLink with ActionTypes to avoid remap

const TooltipAction = props => React.createElement(
  typeof props.action === 'function' ? TooltipButton : TooltipLink,
  Object.assign({}, props, {
    title: props.label,
    [typeof props.action === 'function' ? 'onClick' : 'target']: props.action
  }),
  <span className={props.icon} />
)

TooltipAction.propTypes = ActionTypes.propTypes
TooltipAction.defaultProps = ActionTypes.defaultProps

export {
  TooltipAction
}
