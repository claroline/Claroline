import editor from './editor'
import {AudioContentPlayer} from './player.jsx'

export default {
  type: 'audio',
  icon: 'fa fa-file-audio-o',
  altIcon: 'fa fa-volume-down',
  player: AudioContentPlayer,
  browseFiles: 'audio',
  editor
}
