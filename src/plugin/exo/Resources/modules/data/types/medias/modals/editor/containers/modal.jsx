import {connect} from 'react-redux'

import {actions} from '#/plugin/exo/data/types/medias/modals/editor/store'
import {AddMediaModal as AddMediaModalComponent} from '#/plugin/exo/data/types/medias/modals/editor/components/modal'

const AddMediaModal = connect(
  null,
  (dispatch) => ({
    saveFile(file) {
      return dispatch(actions.saveFile(file)).then((url) => url)
    }
  })
)(AddMediaModalComponent)

export {
  AddMediaModal
}
