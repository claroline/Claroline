import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

import {VideoEditor as VideoEditorComponent} from '#/integration/peertube/resources/video/components/editor'
import {selectors} from '#/integration/peertube/resources/video/store'

const VideoEditor = connect(
  (state) => ({
    path: toolSelectors.path(state),
    video: selectors.video(state)
  })
)(VideoEditorComponent)

export {
  VideoEditor
}
