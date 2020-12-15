import {trans} from '#/main/app/intl/translation'

import {ResourceEditor} from '#/plugin/exo/contents/resource/components/editor'

import {ResourceContentPlayer} from '#/plugin/exo/contents/resource/components/player'
import {ResourceContentThumbnail} from '#/plugin/exo/contents/resource/components/thumbnail'
import {ResourceContentModal} from '#/plugin/exo/contents/resource/components/modal'

export default {
  type: 'resource/*',
  name: 'resource',
  tags: [trans('content')],
  answerable: false,
  fileUpload: true,

  components: {
    editor: ResourceEditor
  },

  player: ResourceContentPlayer,
  thumbnail: ResourceContentThumbnail,
  modal: ResourceContentModal
}
