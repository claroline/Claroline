import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makeResourceReducer} from '#/main/core/resource/reducer'

import {reducer as editorReducer} from '#/plugin/video-player/resources/video/editor/reducer'

const reducer = makeResourceReducer({}, {
  url: makeReducer({}, {}),
  video: makeReducer({}, {}),
  tracks: editorReducer.tracks
})

export {
  reducer
}