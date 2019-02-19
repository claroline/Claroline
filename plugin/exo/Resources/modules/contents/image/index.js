import {trans} from '#/main/app/intl/translation'

import editor from './editor'
import {ImageContentPlayer} from './player.jsx'
import {ImageContentThumbnail} from './thumbnail.jsx'
import {ImageContentModal} from './modal.jsx'

export default {
  type: 'image',
  name: 'image',
  tags: [trans('content')],
  answerable: false,

  icon: 'fa fa-picture-o',
  player: ImageContentPlayer,
  browseFiles: 'image',
  thumbnail:ImageContentThumbnail,
  modal:ImageContentModal,
  editable: false,
  editor
}