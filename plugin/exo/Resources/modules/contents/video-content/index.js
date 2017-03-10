import editor from './editor'
import {VideoContentPlayer} from './player.jsx'
import {VideoContentThumbnail} from './thumbnail.jsx'
import {VideoContentModal} from './modal.jsx'

export default {
  type: 'video',
  icon: 'fa fa-file-video-o',
  altIcon: 'fa fa-video-camera',
  player: VideoContentPlayer,
  browseFiles: 'video',
  thumbnail: VideoContentThumbnail,
  modal: VideoContentModal,
  editable: false,
  editor
}
