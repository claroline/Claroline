/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineAudioPlayerBundle', {
  files: {
    'audio/*': () => { return import(/* webpackChunkName: "plugin-audio-file-audio" */ '#/plugin/audio-player/files/audio') }
  },
  quizItems: {
    'waveform' : () => { return import(/* webpackChunkName: "quiz-item-waveform" */    '#/plugin/audio-player/quiz/items/waveform') }
  }
})
