import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Link} from '#/main/core/layout/button/components/link.jsx'
import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'

/**
 * Link with tooltip.
 *
 * @param props
 * @constructor
 */
const TooltipLink = props =>
  <TooltipElement
    id={props.id}
    position={props.position}
    tip={props.title}
  >
    <Link {...props}>
      {props.children}
    </Link>
  </TooltipElement>

TooltipLink.propTypes = {
  id: T.string.isRequired,
  title: T.string.isRequired,
  children: T.node.isRequired,
  position: T.oneOf(['top', 'right', 'bottom', 'left']),
  disabled: T.bool,
  target: T.string,
  className: T.string
}

TooltipLink.defaultProps = {
  position: 'top',
  disabled: false
}

export {
  TooltipLink
}
