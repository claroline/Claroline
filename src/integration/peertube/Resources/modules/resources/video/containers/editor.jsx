import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {VideoEditor as VideoEditorComponent} from '#/integration/peertube/resources/video/components/editor'
import {selectors} from '#/integration/peertube/resources/video/store'

const VideoEditor = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    workspace: resourceSelectors.workspace(state),
    video: selectors.video(state)
  })
)(VideoEditorComponent)

export {
  VideoEditor
}
