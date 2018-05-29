import {bootstrap} from '#/main/app/bootstrap'
import {registerModals} from '#/main/core/layout/modal'

import {reducer} from '#/plugin/video-player/resources/video/reducer'
import {VideoPlayerResource} from '#/plugin/video-player/resources/video/components/resource.jsx'
import {
  MODAL_VIDEO_SUBTITLES,
  SubtitlesModal
} from '#/plugin/video-player/resources/video/editor/components/modal/subtitles.jsx'

registerModals([
  [MODAL_VIDEO_SUBTITLES, SubtitlesModal]
])

// mount the react application
bootstrap(
  // app DOM container (also holds initial app data as data attributes)
  '.video-player-container',

  // app main component (accepts either a `routedApp` or a `ReactComponent`)
  VideoPlayerResource,

  // app store configuration
  reducer,

  (initialData) => Object.assign({}, initialData, {
    resource: {
      node: initialData.resourceNode,
      evaluation: initialData.evaluation
    }
  })
)