import React, {forwardRef} from 'react'
import {PropTypes as T} from 'prop-types'

import Dropdown from 'react-bootstrap/Dropdown'

// forwardRef is required for tooltip
const MenuOverlay = forwardRef((props, ref) =>
  <Dropdown
    show={props.show}
    drop={props.drop}
    align={props.align}
    autoClose={true}
    className={props.className}
    disabled={props.disabled}
    onToggle={props.onToggle}
    ref={ref}
    style={props.style}
  >
    {props.children}
  </Dropdown>
)

MenuOverlay.propTypes = {
  show: T.bool,
  style: T.object,
  className: T.string,
  disabled: T.bool,
  drop: T.oneOf(['up', 'start', 'end', 'down']),
  align: T.oneOf(['start', 'end']),
  children: T.node.isRequired,
  onToggle: T.func
}

export {
  MenuOverlay
}
