import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CustomDragLayer} from '#/plugin/exo/utils/custom-drag-layer'
import {DragDropProvider} from '#/main/app/overlays/dnd/components/provider'
import {ResourceEditor} from '#/main/core/resource/editor'

import {QuizEditorParameters} from '#/plugin/exo/resources/quiz/editor/containers/parameters'
import {QuizEditorBank} from '#/plugin/exo/resources/quiz/editor/containers/bank'
import {QuizEditorSteps} from '#/plugin/exo/resources/quiz/editor/components/steps'
import {selectors} from '#/plugin/exo/resources/quiz/store'


const QuizEditor = () => {
  const quiz = useSelector(selectors.quiz)

  return (
    <DragDropProvider>
      <ResourceEditor
        styles={['claroline-distribution-plugin-exo-quiz-resource']}
        additionalData={() => ({
          resource: quiz
        })}
        defaultPage="steps"
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
            component: QuizEditorBank
          }
        ]}
      />
      <CustomDragLayer key="drag-layer" />
    </DragDropProvider>
  )
}

export {
  QuizEditor
}
