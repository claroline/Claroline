import {AudioPlayer} from '#/plugin/audio-player/files/audio/components/player'
import {AudioEditor} from '#/plugin/audio-player/files/audio/components/editor'

const fileType = {
  components: {
    player: AudioPlayer,
    editor: AudioEditor
  },
  styles: ['claroline-distribution-plugin-audio-player-resource']
}

export {
  fileType
}
