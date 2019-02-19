import {trans} from '#/main/app/intl/translation'

import editor from './editor'
import {TextContentPlayer} from './player.jsx'
import {TextContentThumbnail} from './thumbnail.jsx'
import {TextContentModal} from './modal.jsx'

export default {
  type: 'text',
  name: 'text',
  tags: [trans('content')],
  answerable: false,

  mimeType: 'text/html',
  icon: 'fa fa-align-justify',
  player: TextContentPlayer,
  thumbnail: TextContentThumbnail,
  modal: TextContentModal,
  editable: true,
  editor
}
