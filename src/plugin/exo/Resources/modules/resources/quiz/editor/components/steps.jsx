import React from 'react'
import {useSelector} from 'react-redux'

import {Routes} from '#/main/app/router'
import {selectors as editorSelectors} from '#/main/core/resource/editor'

import {QuizEditorSummary} from '#/plugin/exo/resources/quiz/editor/containers/summary'
import {QuizEditorStep} from '#/plugin/exo/resources/quiz/editor/components/step'
import get from 'lodash/get'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {MODAL_STEP_POSITION} from '#/plugin/exo/resources/quiz/editor/modals/step-position'

const QuizEditorSteps = (props) => {
  const resourceEditorPath = useSelector(editorSelectors.path) + '/steps'

  function getStepActions(step, index) {
    return [
      {
        name: 'copy',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-clone',
        label: trans('copy', {}, 'actions'),
        modal: [MODAL_STEP_POSITION, {
          icon: 'fa fa-fw fa-clone',
          title: trans('copy'),
          step: {
            id: step.id,
            title: step.title || trans('step', {number: index + 1}, 'quiz')
          },
          steps: props.steps.map((s, i) => ({
            id: s.id,
            title: s.title || trans('step', {number: i + 1}, 'quiz')
          })),
          selectAction: (position) => ({
            type: CALLBACK_BUTTON,
            label: trans('copy', {}, 'actions'),
            callback: () => props.copyStep(step.id, props.steps, position)
          })
        }],
        group: trans('management')
      }, {
        name: 'move',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-arrows',
        label: trans('move', {}, 'actions'),
        modal: [MODAL_STEP_POSITION, {
          icon: 'fa fa-fw fa-arrows',
          title: trans('movement'),
          step: {
            id: step.id,
            title: step.title || trans('step', {number: index + 1}, 'quiz')
          },
          steps: props.steps.map((s, i) => ({
            id: s.id,
            title: s.title || trans('step', {number: i + 1}, 'quiz')
          })),
          selectAction: (position) => ({
            type: CALLBACK_BUTTON,
            label: trans('move', {}, 'actions'),
            callback: () => props.moveStep(step.id, position)
          })
        }],
        group: trans('management')
      }, {
        name: 'delete',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-trash',
        label: trans('delete', {}, 'actions'),
        callback: () => {
          props.removeStep(step.id)
          if (`${props.path}/edit/${step.slug}` === props.location.pathname) {
            props.history.push(`${props.path}/edit`)
          }
        },
        confirm: {
          title: trans('deletion'),
          subtitle: step.title || trans('step', {number: index + 1}, 'quiz'),
          message: trans('remove_step_confirm_message', {}, 'quiz')
        },
        dangerous: true,
        group: trans('management')
      }
    ]
  }

  return (
    <Routes
      path={resourceEditorPath}
      routes={[
        {
          path: '/',
          exact: true,
          component: QuizEditorSummary
        }, {
          path: '/:slug',
          component: QuizEditorStep,
          render: (routeProps) => {
            const stepIndex = props.steps.findIndex(step => routeProps.match.params.slug === step.slug)
            if (-1 !== stepIndex) {
              const currentStep = props.steps[stepIndex]

              return (
                <QuizEditorStep
                  formName={props.formName}
                  path={`steps[${stepIndex}]`}
                  numberingType={props.numberingType}
                  questionNumberingType={props.questionNumberingType}
                  steps={props.steps}
                  index={stepIndex}
                  id={currentStep.id}
                  title={currentStep.title}
                  hasExpectedAnswers={props.hasExpectedAnswers}
                  score={props.score}
                  items={currentStep.items}
                  errors={get(props.errors, `steps[${stepIndex}]`)}
                  actions={getStepActions(currentStep, stepIndex)}
                  update={(prop, value) => props.update(`steps[${stepIndex}].${prop}`, value)}
                  moveItem={(itemId, position) => props.moveItem(itemId, position)}
                  copyItem={(itemId, position) => props.copyItem(itemId, position)}
                />
              )
            }

            routeProps.history.push(`${props.path}/edit`)

            return null
          }
        }
      ]}
    />
  )
}

export {
  QuizEditorSteps
}
