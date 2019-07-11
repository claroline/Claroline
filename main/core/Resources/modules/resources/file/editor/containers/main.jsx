import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {selectors as fileSelectors} from '#/main/core/resources/file/store'
import {EditorMain as EditorMainComponent} from '#/main/core/resources/file/editor/components/main'

const EditorMain = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    mimeType: fileSelectors.mimeType(state),
    file: fileSelectors.file(state)
  })
)(EditorMainComponent)

export {
  EditorMain
}
