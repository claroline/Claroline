import {makeReducer} from '#/main/app/store/reducer'

import {reducer as editorReducer} from '#/plugin/video-player/resources/video/editor/reducer'

const reducer = {
  url: makeReducer({}, {}),
  video: makeReducer({}, {}),
  tracks: editorReducer.tracks
}

export {
  reducer
}