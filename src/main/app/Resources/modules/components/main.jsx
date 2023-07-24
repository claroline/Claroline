import React from 'react'
import {PropTypes as T} from 'prop-types'
import {Provider} from 'react-redux'

import {Router} from '#/main/app/router'
import {DragDropProvider} from '#/main/app/overlays/dnd/components/provider'

// implemented overlays
import {ModalOverlay} from '#/main/app/overlays/modal/containers/overlay'
import {AlertOverlay} from '#/main/app/overlays/alert/containers/overlay'

const Main = props =>
  <Provider store={props.store}>
    <DragDropProvider>
      <Router basename={props.defaultPath} embedded={props.embedded}>
        <AlertOverlay key="alert" />

        {props.children}

        <ModalOverlay key="modal" />
      </Router>
    </DragDropProvider>
  </Provider>

Main.propTypes = {
  defaultPath: T.string,
  embedded: T.bool,
  store: T.object.isRequired,
  children: T.any
}

Main.defaultProps = {
  embedded: false
}

export {
  Main
}
