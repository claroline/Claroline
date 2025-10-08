import React from 'react'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {ResourceEditorAppearance} from '#/main/core/resource/editor/components/appearance'

import {NUMBERINGS} from '#/main/app/utils/numbering'
import {constants} from '#/main/evaluation/sequence/constants'

const LessonEditorAppearance = () =>
  <ResourceEditorAppearance
    definition={[
      {
        name: 'lesson-display',
        icon: 'fa fa-fw fa-desktop',
        title: trans('pages'),
        primary: true,
        hideTitle: true,
        fields: [
          {
            name: 'resource.display.showOverview',
            type: 'boolean',
            label: trans('show_overview', {}, 'lesson'),
            help: trans('show_overview_help', {}, 'lesson')
          }, {
            name: 'resource.display.pagination',
            type: 'choice',
            label: trans('sequence_pagination', {}, 'evaluation'),
            required: true,
            options: {
              noEmpty: true,
              condensed: false,
              choices: constants.PAGINATIONS
            }
          }, {
            name: 'resource.display.navigation',
            type: 'boolean',
            label: trans('show_navigation', {}, 'lesson'),
            help: trans('show_navigation_help', {}, 'lesson'),
            displayed: (formData) => 'none' !== get(formData, 'resource.display.pagination')
          }, {
            name: 'resource.display.showMeta',
            type: 'boolean',
            label: trans('show_metadata', {}, 'lesson'),
            help: trans('show_metadata_help', {}, 'lesson')
          }, {
            name: 'resource.display.numbering',
            type: 'choice',
            label: trans('lesson_numbering', {}, 'lesson'),
            required: true,
            options: {
              noEmpty: true,
              condensed: false,
              choices: NUMBERINGS
            }
          }
        ]
      }
    ]}
  />

export {
  LessonEditorAppearance
}
