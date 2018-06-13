import React from 'react'
import {PropTypes as T} from 'prop-types'

import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'

import {Popover} from '#/main/app/overlay/popover/components/popover'

const PopoverOverlay = props => !props.disabled ?
  <OverlayTrigger
    trigger="click"
    placement={props.position}
    rootClose={true}
    overlay={
      <Popover
        id={props.id}
        className={props.className}
        title={props.title}
      >
        {props.content}
      </Popover>
    }
  >
    {props.children}
  </OverlayTrigger>
  :
  props.children

PopoverOverlay.propTypes = {
  id: T.string.isRequired,
  disabled: T.bool,
  children: T.element.isRequired,
  className: T.string,
  title: T.node,
  content: T.node.isRequired,
  position: T.oneOf(['top', 'right', 'bottom', 'left'])
}

PopoverOverlay.defaultProps = {
  position: 'top',
  disabled: false
}

export {
  PopoverOverlay
}
