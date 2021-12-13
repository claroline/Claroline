import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'
import {actions} from '#/plugin/drop-zone/resources/dropzone/player/store/actions'
import {actions as correctionActions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'
import {MyDrop as MyDropComponent} from '#/plugin/drop-zone/resources/dropzone/player/components/my-drop'

const MyDrop = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    currentUser: securitySelectors.currentUser(state),
    dropzone: selectors.dropzone(state),
    myDrop: selectors.myDrop(state),
    isDropEnabled: selectors.isDropEnabled(state),
    currentRevisionId: selectors.currentRevisionId(state),
    revision: selectors.revision(state)
  }),
  (dispatch) => ({
    saveDocument(dropId, dropType, dropData) {
      dispatch(actions.saveDocument(dropId, dropType, dropData))
    },
    deleteDocument(documentId) {
      dispatch(actions.deleteDocument(documentId))
    },
    submit(id) {
      dispatch(actions.submitDrop(id))
    },
    submitForRevision(id) {
      dispatch(actions.submitDropForRevision(id))
    },
    denyCorrection(correctionId, comment) {
      dispatch(correctionActions.denyCorrection(correctionId, comment))
    },
    saveRevisionComment(revisionId, comment) {
      dispatch(actions.saveRevisionComment(revisionId, comment))
    },
    saveDropComment(dropId, comment) {
      dispatch(actions.saveDropComment(dropId, comment, true))
    }
  })
)(MyDropComponent)

export {
  MyDrop
}
