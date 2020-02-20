import {connect} from 'react-redux'

import {PdfPlayer as PdfPlayerComponent} from '#/plugin/pdf-player/files/pdf/components/player'
import {actions} from '#/plugin/pdf-player/files/pdf/store'

/**
 * Connected container for resources.
 *
 * Connects the <Resource> component to a redux store.
 * If you don't use redux in your implementation @see Resource functional component.
 */
const PdfPlayer = connect(
  null,
  (dispatch) => ({
    updateProgression(id, currentPage, totalPage) {
      dispatch(actions.updateProgression(id, currentPage, totalPage))
    }
  })
)(PdfPlayerComponent)

export {
  PdfPlayer
}
