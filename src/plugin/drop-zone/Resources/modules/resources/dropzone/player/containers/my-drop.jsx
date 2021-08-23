import {connect} from 'react-redux'

import {trans} from '#/main/app/intl'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
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
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          icon: 'fa fa-fw fa-exclamation-triangle',
          title: trans('final_drop', {}, 'dropzone'),
          question: trans('submit_my_drop_confirm', {}, 'dropzone'),
          confirmButtonText: trans('submit_my_drop', {}, 'dropzone'),
          dangerous: true,
          handleConfirm: () => dispatch(actions.submitDrop(id))
        })
      )
    },
    submitForRevision(id) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          icon: 'fa fa-fw fa-exclamation-triangle',
          title: trans('submit_for_revision', {}, 'dropzone'),
          question: trans('submit_for_revision_confirm', {}, 'dropzone'),
          confirmButtonText: trans('submit_for_revision', {}, 'dropzone'),
          handleConfirm: () => dispatch(actions.submitDropForRevision(id))
        })
      )
    },
    denyCorrection: (correctionId, comment) => dispatch(correctionActions.denyCorrection(correctionId, comment)),
    showModal: (type, props) => dispatch(modalActions.showModal(type, props)),
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
