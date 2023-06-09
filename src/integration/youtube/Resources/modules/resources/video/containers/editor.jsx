import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {VideoEditor as VideoEditorComponent} from '#/integration/youtube/resources/video/components/editor'
import {selectors} from '#/integration/youtube/resources/video/store'

const VideoEditor = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    workspace: resourceSelectors.workspace(state),
    video: selectors.video(state),
  }),
)(VideoEditorComponent)

export {
  VideoEditor
}
