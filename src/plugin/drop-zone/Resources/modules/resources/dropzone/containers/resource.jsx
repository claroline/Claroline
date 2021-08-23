import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {DropzoneResource as DropzoneResourceComponent} from '#/plugin/drop-zone/resources/dropzone/components/resource'
import {reducer, selectors} from '#/plugin/drop-zone/resources/dropzone/store'
import {actions as playerActions} from '#/plugin/drop-zone/resources/dropzone/player/store/actions'
import {actions as correctionActions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'

const DropzoneResource = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
        dropzone: selectors.dropzone(state),
        myDrop: selectors.myDrop(state),
        currentRevisionId: selectors.currentRevisionId(state)
      }),
      (dispatch) => ({
        resetForm: (formData) => dispatch(formActions.resetForm(selectors.STORE_NAME+'.dropzoneForm', formData)),

        fetchDrop: (dropId, type) => dispatch(correctionActions.fetchDrop(dropId, type)),
        resetCurrentDrop: () => dispatch(correctionActions.resetCurrentDrop()),
        fetchCorrections: (dropzoneId) => dispatch(correctionActions.fetchCorrections(dropzoneId)),
        resetCorrectorDrop: () => dispatch(correctionActions.resetCorrectorDrop()),
        fetchPeerDrop: () => dispatch(playerActions.fetchPeerDrop()),
        fetchRevision: (revisionId) => dispatch(playerActions.fetchRevision(revisionId)),
        fetchDropFromRevision: (revisionId) => dispatch(playerActions.fetchDropFromRevision(revisionId)),
        resetRevision: () => dispatch(playerActions.resetRevision())
      })
    )(DropzoneResourceComponent)
  )
)

export {
  DropzoneResource
}
