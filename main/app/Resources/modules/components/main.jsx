import React from 'react'
import {PropTypes as T} from 'prop-types'
import {Provider} from 'react-redux'

import {Router} from '#/main/app/router'
import {OverlayStack} from '#/main/app/overlays/containers/stack'

import {DragDropProvider} from '#/main/app/overlays/dnd/components/provider'
import {FileDrop} from '#/main/app/overlays/dnd/components/file-drop'

// implemented overlays
import {ModalOverlay} from '#/main/app/overlays/modal/containers/overlay'
import {AlertOverlay} from '#/main/app/overlays/alert/containers/overlay'
import {WalkthroughOverlay} from '#/main/app/overlays/walkthrough/containers/overlay'

// TODO : maybe append app styles here

const Main = props =>
  <Provider store={props.store}>
    <DragDropProvider>
      <FileDrop>
        <Router basename={props.defaultPath} embedded={props.embedded}>
          <AlertOverlay key="alert" />

          {props.children}

          <OverlayStack>
            <ModalOverlay key="modal" />
            <WalkthroughOverlay key="walkthrough" />
          </OverlayStack>
        </Router>
      </FileDrop>
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
