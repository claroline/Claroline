import editor from './editor'
import {TextContentPlayer} from './player.jsx'
import {TextContentThumbnail} from './thumbnail.jsx'
import {TextContentModal} from './modal.jsx'

export default {
  type: 'text',
  mimeType: 'text/html',
  icon: 'fa fa-align-left',
  altIcon: 'fa fa-align-left',
  player: TextContentPlayer,
  thumbnail: TextContentThumbnail,
  modal: TextContentModal,
  editable: true,
  editor
}
