import {trans} from '#/main/app/intl/translation'

import editor from './editor'
import {AudioContentPlayer} from './player.jsx'
import {AudioContentThumbnail} from './thumbnail.jsx'
import {AudioContentModal} from './modal.jsx'

export default {
  type: 'audio',
  name: 'audio',
  tags: [trans('content')],
  answerable: false,

  icon: 'fa fa-volume-up',
  player: AudioContentPlayer,
  browseFiles: 'audio',
  thumbnail: AudioContentThumbnail,
  modal: AudioContentModal,
  editable: false,
  editor
}
