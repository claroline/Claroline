import {makeReducer} from '#/main/core/scaffolding/reducer'

import {reducer as editorReducer} from '#/plugin/video-player/resources/video/editor/reducer'

const reducer = {
  url: makeReducer({}, {}),
  video: makeReducer({}, {}),
  tracks: editorReducer.tracks
}

export {
  reducer
}