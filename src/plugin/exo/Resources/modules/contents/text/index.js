import {trans} from '#/main/app/intl/translation'

import {TextEditor} from '#/plugin/exo/contents/text/components/editor'

import {TextContentPlayer} from '#/plugin/exo/contents/text/components/player'
import {TextContentThumbnail} from '#/plugin/exo/contents/text/components/thumbnail'
import {TextContentModal} from '#/plugin/exo/contents/text/components/modal'

export default {
  type: 'text/html',
  name: 'text',
  tags: [trans('content')],
  answerable: false,

  components: {
    editor: TextEditor
  },

  player: TextContentPlayer,
  thumbnail: TextContentThumbnail,
  modal: TextContentModal
}
