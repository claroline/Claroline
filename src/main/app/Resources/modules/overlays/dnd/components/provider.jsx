import React from 'react'
import {PropTypes as T} from 'prop-types'

import {DragDropContextProvider} from 'react-dnd'
import TouchBackend from 'react-dnd-touch-backend'

const DragDropProvider = props =>
  <DragDropContextProvider backend={TouchBackend({ enableMouseEvents: true })}>
    {props.children}
  </DragDropContextProvider>

DragDropProvider.propTypes = {
  children: T.node.isRequired
}

export {
  DragDropProvider
}
