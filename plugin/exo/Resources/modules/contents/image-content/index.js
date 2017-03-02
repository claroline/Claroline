import editor from './editor'
import {ImageContentPlayer} from './player.jsx'

export default {
  type: 'image',
  icon: 'fa fa-file-image-o',
  altIcon: 'fa fa-picture-o',
  player: ImageContentPlayer,
  browseFiles: 'image',
  editor
}