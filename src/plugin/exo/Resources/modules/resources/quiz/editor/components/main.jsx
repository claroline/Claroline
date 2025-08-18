import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {ResourceEditor} from '#/main/core/resource/editor'

import {QuizEditorParameters} from '#/plugin/exo/resources/quiz/editor/containers/parameters'
import {QuizEditorBank} from '#/plugin/exo/resources/quiz/editor/containers/bank'
import {QuizEditorSteps} from '#/plugin/exo/resources/quiz/editor/containers/steps'
import {selectors} from '#/plugin/exo/resources/quiz/store'
import {QuizEditorAppearance} from '#/plugin/exo/resources/quiz/editor/components/appearance'

const QuizEditor = () => {
  const quiz = useSelector(selectors.quiz)

  return (
    <ResourceEditor
      styles={['claroline-distribution-plugin-exo-quiz-resource']}
      additionalData={() => ({
        resource: quiz
      })}
      appearancePage={QuizEditorAppearance}
      pages={[
        {
          name: 'parameters',
          title: trans('parameters'),
          component: QuizEditorParameters
        }, {
          name: 'steps',
          title: trans('steps', {}, 'quiz'),
          component: QuizEditorSteps
        }, {
          name: 'bank',
          title: trans('questions_bank', {}, 'quiz'),
          component: QuizEditorBank,
          displayed: false
        }
      ]}
    />
  )
}

export {
  QuizEditor
}
