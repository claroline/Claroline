import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {EditorMain as EditorMainComponent} from '#/main/core/resources/directory/editor/components/main'
import {selectors} from '#/main/core/resources/directory/editor/store'
import {selectors as directorySelectors} from '#/main/core/resources/directory/store'

const EditorMain = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    currentUser: securitySelectors.currentUser(state),
    directory: selectors.directory(state),
    workspace: resourceSelectors.workspace(state),
    storageLock: directorySelectors.storageLock(state)
  })
)(EditorMainComponent)

export {
  EditorMain
}
