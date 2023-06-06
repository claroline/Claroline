import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

import {VideoEditor as VideoEditorComponent} from '#/integration/youtube/resources/video/components/editor'
import {selectors} from '#/integration/youtube/resources/video/store'

const VideoEditor = connect(
  (state) => ({
    path: toolSelectors.path(state),
    video: selectors.video(state)
  })
)(VideoEditorComponent)

export {
  VideoEditor
}
