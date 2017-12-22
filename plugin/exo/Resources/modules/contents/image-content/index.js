import editor from './editor'
import {ImageContentPlayer} from './player.jsx'
import {ImageContentThumbnail} from './thumbnail.jsx'
import {ImageContentModal} from './modal.jsx'

export default {
  type: 'image',
  icon: 'fa fa-picture-o',
  player: ImageContentPlayer,
  browseFiles: 'image',
  thumbnail:ImageContentThumbnail,
  modal:ImageContentModal,
  editable: false,
  editor
}