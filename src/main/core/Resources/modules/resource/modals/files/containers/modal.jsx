import {connect} from 'react-redux'

import {actions} from '#/main/core/resource/modals/files/store'
import {ResourceFilesCreationModal as ResourceFilesCreationModalComponent} from '#/main/core/resource/modals/files/components/modal'

const ResourceFilesCreationModal = connect(
  null,
  (dispatch) => ({
    createFiles(parent, files, callback) {
      dispatch(actions.createFiles(parent, files, callback))
    }
  })
)(ResourceFilesCreationModalComponent)

export {
  ResourceFilesCreationModal
}
