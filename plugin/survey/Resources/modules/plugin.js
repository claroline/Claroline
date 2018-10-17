/* eslint-disable */

import {registry} from '#/main/app/plugins/registry'

registry.add('survey', {
  actions: {
    'open_survey' : () => { return import(/* webpackChunkName: "plugin-survey-action-open" */ '#/plugin/survey/resources/survey/actions/open') }
  }
})
