import {connect} from 'react-redux'
import {actions as resourceActions} from '#/main/core/resource/store/actions'

// the component to connect
import {PdfPlayer as PdfPlayerComponent} from '#/plugin/pdf-player/files/pdf/components/player'

/**
 * Connected container for resources.
 *
 * Connects the <Resource> component to a redux store.
 * If you don't use redux in your implementation @see Resource functional component.
 */
const PdfPlayer = connect(
  null,
  (dispatch) => ({
    setErrors() {
      dispatch(resourceActions.setServerErrors(['file_not_found']))
    }
  })
)(PdfPlayerComponent)

export {
  PdfPlayer
}
