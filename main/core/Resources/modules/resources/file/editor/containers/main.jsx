import {connect} from 'react-redux'

import {EditorMain as EditorMainComponent} from '#/main/core/resources/file/editor/components/main'
import {selectors} from '#/main/core/resources/file/editor/store/selectors'

const EditorMain = connect(
  (state) => ({
    mimeType: selectors.mimeType(state),
    file: selectors.file(state)
  })
)(EditorMainComponent)

export {
  EditorMain
}
