import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'
import uniqBy from 'lodash/uniqBy'

import {trans} from '#/main/app/intl'
import {Routes} from '#/main/app/router'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {selectors as editorSelectors} from '#/main/core/resource/editor'

import {QuizEditorSummary} from '#/plugin/exo/resources/quiz/editor/containers/summary'
import {QuizEditorStep} from '#/plugin/exo/resources/quiz/editor/components/step'
import {MODAL_STEP_POSITION} from '#/plugin/exo/resources/quiz/editor/modals/step-position'
import {MODAL_ITEM_IMPORT} from '#/plugin/exo/items/modals/import'
import {MODAL_ITEM_CREATION} from '#/plugin/exo/items/modals/creation'

const QuizEditorSteps = (props) => {
  const resourceEditorPath = useSelector(editorSelectors.path) + '/steps'

  function getStepActions(step, index) {
    return [
      {
        name: 'add-item',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_question_from_new', {}, 'quiz'),
        modal: [MODAL_ITEM_CREATION, {
          create: (item) => {
            if (!props.hasExpectedAnswers) {
              item.hasExpectedAnswers = false
            }

            if (!props.hasExpectedAnswers || !props.score || 'none' === props.score.type) {
              item.score = {
                type: 'none'
              }
            }

            props.update('items', [].concat(props.items, [item]))
            props.history.push(`${resourceEditorPath}/${step.slug}/${item.id}`)
          }
        }],
        primary: true
      }, {
        name: 'import-item',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('add_question_from_existing', {}, 'quiz'),
        modal: [MODAL_ITEM_IMPORT, {
          selectAction: (items) => ({
            type: CALLBACK_BUTTON,
            callback: () => {
              // append some quiz parameters to the item
              items = items.map(item => {
                if (!props.hasExpectedAnswers) {
                  item.hasExpectedAnswers = false
                }

                if (!props.hasExpectedAnswers || !props.score || 'none' === props.score.type) {
                  item.score = {
                    type: 'none'
                  }
                }

                return item
              })

              props.update('items', uniqBy([].concat(step.items, items), (item) => item.id))
              props.history.push(`${resourceEditorPath}/${step.slug}/${item.id}`)
            }
          })
        }]
      }, {
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
            callback: () => props.copyStep(props.steps, step.id, position)
          })
        }],
        //group: trans('management')
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
            callback: () => props.moveStep(props.steps, step.id, position)
          })
        }],
        //group: trans('management')
      }, {
        name: 'delete',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-trash',
        label: trans('delete', {}, 'actions'),
        callback: () => {
          props.removeStep(props.steps, step.id)
          if (`${resourceEditorPath}/${step.slug}` === props.location.pathname) {
            props.history.push(resourceEditorPath)
          }
        },
        confirm: {
          title: trans('deletion'),
          subtitle: step.title || trans('step', {number: index + 1}, 'quiz'),
          message: trans('remove_step_confirm_message', {}, 'quiz')
        },
        dangerous: true,
        //group: trans('management')
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
          render: () => (
            <QuizEditorSummary
              getStepActions={getStepActions}
            />
          )
        }, {
          path: '/:slug/:itemId?',
          render: (routeProps) => {
            const stepIndex = props.steps.findIndex(step => routeProps.match.params.slug === step.slug)
            if (-1 !== stepIndex) {
              const currentStep = props.steps[stepIndex]

              return (
                <QuizEditorStep
                  formName={editorSelectors.STORE_NAME}
                  path={`resource.steps[${stepIndex}]`}
                  numberingType={props.numberingType}
                  questionNumberingType={props.questionNumberingType}
                  steps={props.steps}
                  index={stepIndex}
                  id={currentStep.id}
                  title={currentStep.title}
                  currentItemId={routeProps.match.params.itemId}
                  hasExpectedAnswers={props.hasExpectedAnswers}
                  score={props.score}
                  items={currentStep.items}
                  errors={get(props.errors, `resource.steps[${stepIndex}]`)}
                  actions={getStepActions(currentStep, stepIndex)}
                  update={(prop, value) => props.update(`steps[${stepIndex}].${prop}`, value)}
                  moveItem={(itemId, position) => props.moveItem(props.steps, itemId, position)}
                  copyItem={(itemId, position) => props.copyItem(props.steps, itemId, position)}
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
