import React from 'react'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {ResourceEditorAppearance} from '#/main/core/resource/editor/components/appearance'

import {constants} from '#/plugin/exo/resources/quiz/constants'

const QuizEditorAppearance = () =>
  <ResourceEditorAppearance
    definition={[
      {
        name: 'numbering',
        //icon: 'fa fa-fw fa-desktop',
        title: trans('Titres et numérotation'),
        primary: true,
        fields: [
          {
            name: 'resource.parameters.showTitles',
            type: 'boolean',
            label: trans('show_step_titles', {}, 'quiz'),
            linked: [
              {
                name: 'resource.parameters.numbering',
                type: 'choice',
                label: trans('quiz_numbering', {}, 'quiz'),
                required: true,
                displayed: (quiz) => get(quiz, 'resource.parameters.showTitles', false),
                options: {
                  noEmpty: true,
                  condensed: true,
                  choices: constants.QUIZ_NUMBERINGS
                }
              }
            ]
          }, {
            name: 'resource.parameters.showQuestionTitles',
            type: 'boolean',
            label: trans('show_question_titles', {}, 'quiz'),
            linked: [
              {
                name: 'resource.parameters.questionNumbering',
                type: 'choice',
                label: trans('quiz_question_numbering', {}, 'quiz'),
                required: true,
                displayed: (quiz) => get(quiz, 'resource.parameters.showQuestionTitles', false),
                options: {
                  noEmpty: true,
                  condensed: true,
                  choices: constants.QUIZ_NUMBERINGS
                }
              }
            ]
          }
        ]
      }
    ]}
  />

export {
  QuizEditorAppearance
}
