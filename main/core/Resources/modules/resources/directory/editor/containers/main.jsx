import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {EditorMain as EditorMainComponent} from '#/main/core/resources/directory/editor/components/main'
import {selectors} from '#/main/core/resources/directory/editor/store'
import {selectors as directorySelectors} from '#/main/core/resources/directory/store'

const EditorMain = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    directory: selectors.directory(state),
    storageLock: directorySelectors.storageLock(state)
  })
)(EditorMainComponent)

export {
  EditorMain
}
