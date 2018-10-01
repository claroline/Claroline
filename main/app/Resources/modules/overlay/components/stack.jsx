import React from 'react'
import {PropTypes as T} from 'prop-types'
import {Portal} from 'react-overlays'
import classes from 'classnames'

const OverlayStack = props =>
  <Portal container={() => document.querySelector('.app-overlays-container')}>
    <div className={classes('app-overlays', props.show && 'overlay-open')} style={!props.show ? {display: 'none'} : undefined}>
      {props.children}
    </div>
  </Portal>

OverlayStack.propTypes = {
  show: T.bool,
  children: T.oneOfType([T.node, T.arrayOf(T.node)]).isRequired
}

OverlayStack.defaultProps = {
  show: false
}

export {
  OverlayStack
}
