import {trans} from '#/main/app/intl/translation'

import {VideoEditor} from '#/plugin/exo/contents/video/components/editor'

import {VideoContentPlayer} from '#/plugin/exo/contents/video/components/player'
import {VideoContentThumbnail} from '#/plugin/exo/contents/video/components/thumbnail'
import {VideoContentModal} from '#/plugin/exo/contents/video/components/modal'

export default {
  type: 'video',
  name: 'video',
  tags: [trans('content')],
  answerable: false,

  components: {
    editor: VideoEditor
  },

  player: VideoContentPlayer,
  thumbnail: VideoContentThumbnail,
  modal: VideoContentModal
}
