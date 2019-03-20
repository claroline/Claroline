import {trans} from '#/main/app/intl/translation'

import {AudioEditor} from '#/plugin/exo/contents/audio/components/editor'

import {AudioContentPlayer} from '#/plugin/exo/contents/audio/components/player'
import {AudioContentThumbnail} from '#/plugin/exo/contents/audio/components/thumbnail'
import {AudioContentModal} from '#/plugin/exo/contents/audio/components/modal'

export default {
  type: 'audio',
  name: 'audio',
  tags: [trans('content')],
  answerable: false,

  components: {
    editor: AudioEditor
  },

  player: AudioContentPlayer,
  thumbnail: AudioContentThumbnail,
  modal: AudioContentModal
}
