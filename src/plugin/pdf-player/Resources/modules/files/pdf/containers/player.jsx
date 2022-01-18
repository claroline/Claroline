import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {PdfPlayer as PdfPlayerComponent} from '#/plugin/pdf-player/files/pdf/components/player'
import {actions} from '#/plugin/pdf-player/files/pdf/store'

/**
 * Connected container for resources.
 *
 * Connects the <Resource> component to a redux store.
 * If you don't use redux in your implementation @see Resource functional component.
 */
const PdfPlayer = connect(
  (state) => ({
    nodeId: resourceSelectors.id(state),
    currentUser: securitySelectors.currentUser(state)
  }),
  (dispatch) => ({
    updateProgression(id, currentPage, totalPage) {
      dispatch(actions.updateProgression(id, currentPage, totalPage))
    }
  })
)(PdfPlayerComponent)

export {
  PdfPlayer
}
