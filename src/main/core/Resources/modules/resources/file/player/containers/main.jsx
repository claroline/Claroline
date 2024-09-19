import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store/selectors'

import {PlayerMain as PlayerMainComponent} from '#/main/core/resources/file/player/components/main'
import {actions, selectors} from '#/main/core/resources/file/store'

const PlayerMain = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    currentUser: securitySelectors.currentUser(state),
    mimeType: selectors.mimeType(state),
    file: selectors.file(state),
    resourceNode: resourceSelectors.resourceNode(state),
    embedded: resourceSelectors.embedded(state),
    workspace: resourceSelectors.workspace(state),
    canEdit: hasPermission('edit', resourceSelectors.resourceNode(state))
  }),
  (dispatch) => ({
    download(resourceNode) {
      dispatch(actions.download(resourceNode))
    }
  })
)(PlayerMainComponent)

export {
  PlayerMain
}
