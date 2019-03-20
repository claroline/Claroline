import {trans} from '#/main/app/intl/translation'

import {ImageEditor} from '#/plugin/exo/contents/image/components/editor'

import {ImageContentPlayer} from '#/plugin/exo/contents/image/components/player'
import {ImageContentThumbnail} from '#/plugin/exo/contents/image/components/thumbnail'
import {ImageContentModal} from '#/plugin/exo/contents/image/components/modal'

export default {
  type: 'image',
  name: 'image',
  tags: [trans('content')],
  answerable: false,

  components: {
    editor: ImageEditor
  },

  player: ImageContentPlayer,
  thumbnail:ImageContentThumbnail,
  modal:ImageContentModal
}
