import {trans} from '#/main/app/intl/translation'

import editor from './editor'
import {VideoContentPlayer} from './player.jsx'
import {VideoContentThumbnail} from './thumbnail.jsx'
import {VideoContentModal} from './modal.jsx'

export default {
  type: 'video',
  name: 'video',
  tags: [trans('content')],
  answerable: false,

  icon: 'fa fa-video-camera',
  player: VideoContentPlayer,
  browseFiles: 'video',
  thumbnail: VideoContentThumbnail,
  modal: VideoContentModal,
  editable: false,
  editor
}
