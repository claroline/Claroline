import editor from './editor'
import {AudioContentPlayer} from './player.jsx'
import {AudioContentThumbnail} from './thumbnail.jsx'
import {AudioContentModal} from './modal.jsx'

export default {
  type: 'audio',
  icon: 'fa fa-volume-up',
  player: AudioContentPlayer,
  browseFiles: 'audio',
  thumbnail: AudioContentThumbnail,
  modal: AudioContentModal,
  editable: false,
  editor
}
