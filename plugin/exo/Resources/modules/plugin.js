import {registry} from '#/main/app/plugins/registry'

/**
 * Declares applications provided by the Quiz plugin.
 */
registry.add('quiz', {
  resources: {
    'ujm_exercise': () => { return import(/* webpackChunkName: "plugin-exo-quiz-resource" */ '#/plugin/exo/resources/quiz') }
  }
})
