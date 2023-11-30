import {registry} from '#/main/app/plugins/registry'

registry.add('ClarolineFlashcardBundle', {
  resources: {
    'flashcard': () => { return import(/* webpackChunkName: "plugin-flashcard-flashcard-resource" */ '#/plugin/flashcard/resources/flashcard') }
  }
})
