import React from 'react'
import {ResourceEditorAppearance} from '#/main/core/resource/editor/components/appearance'
import {trans} from '#/main/app/intl'
import {constants} from '#/plugin/lesson/resources/lesson/constants'

const LessonEditorAppearance = () =>
  <ResourceEditorAppearance
    definition={[
      {
        name: 'numbering',
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        primary: true,
        hideTitle: true,
        fields: [
          {
            name: 'resource.display.numbering',
            type: 'choice',
            label: trans('lesson_numbering', {}, 'lesson'),
            required: true,
            options: {
              noEmpty: true,
              condensed: false,
              choices: constants.LESSON_NUMBERINGS
            }
          }
        ]
      }
    ]}
  />

export {
  LessonEditorAppearance
}
