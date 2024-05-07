import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {PdfFile as PdfFileComponent} from '#/plugin/pdf-player/files/pdf/components/file'
import {actions} from '#/plugin/pdf-player/files/pdf/store'

/**
 * Connected container for resources.
 *
 * Connects the <Resource> component to a redux store.
 * If you don't use redux in your implementation @see Resource functional component.
 */
const PdfFile = connect(
  (state) => ({
    nodeId: resourceSelectors.id(state),
    currentUser: securitySelectors.currentUser(state)
  }),
  (dispatch) => ({
    loadFile(url) {
      return dispatch(actions.loadFile(url))
    },
    updateProgression(id, currentPage, totalPage) {
      dispatch(actions.updateProgression(id, currentPage, totalPage))
    }
  })
)(PdfFileComponent)

export {
  PdfFile
}
