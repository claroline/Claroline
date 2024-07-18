import React from 'react'
import {PropTypes as T} from 'prop-types'
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
import {MODAL_ITEM_POSITION} from '#/plugin/exo/resources/quiz/editor/modals/item-position'

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

            props.addItem(props.steps, step.id, item)
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
        }]
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
        }]
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
        dangerous: true
      }
    ]
  }

  function getItemActions(step, stepIndex, item, itemIndex) {
    return [
      {
        name: 'copy',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-clone',
        label: trans('copy', {}, 'actions'),
        modal: [MODAL_ITEM_POSITION, {
          icon: 'fa fa-fw fa-arrows',
          title: trans('copy'),
          step: {
            id: step.id,
            title: step.title || trans('step', {number: stepIndex + 1}, 'quiz')
          },
          steps: (props.steps || []).map((s, i) => ({
            id: s.id,
            title: s.title || trans('step', {number: i + 1}, 'quiz'),
            items: s.items
          })),
          item: {
            id: item.id,
            title: item.title || trans('item', {number: itemIndex + 1}, 'quiz')
          },
          selectAction: (position) => ({
            type: CALLBACK_BUTTON,
            label: trans('copy', {}, 'actions'),
            callback: () => props.copyItem(props.steps, item, position)
          })
        }]
      }, {
        name: 'move',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-arrows',
        label: trans('move', {}, 'actions'),
        modal: [MODAL_ITEM_POSITION, {
          icon: 'fa fa-fw fa-arrows',
          title: trans('movement'),
          step: {
            id: step.id,
            title: step.title || trans('step', {number: stepIndex + 1}, 'quiz')
          },
          steps: (props.steps || []).map((s, i) => ({
            id: s.id,
            title: s.title || trans('step', {number: i + 1}, 'quiz'),
            items: s.items
          })),
          item: {
            id: item.id,
            title: item.title || trans('item', {number: itemIndex + 1}, 'quiz')
          },
          selectAction: (position) => ({
            type: CALLBACK_BUTTON,
            label: trans('move', {}, 'actions'),
            callback: () => props.moveItem(props.steps, item.id, position)
          })
        }]
      }, {
        name: 'delete',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-trash',
        label: trans('delete', {}, 'actions'),
        callback: () => props.removeItem(props.steps, item.id),
        confirm: {
          title: trans('deletion'),
          subtitle: item.title || trans('item', {number: itemIndex + 1}, 'quiz'),
          message: trans('remove_item_confirm_message', {}, 'quiz')
        },
        dangerous: true
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
              addStep={() => {
                const newSlug = props.addStep(props.steps)
                props.history.push(`${resourceEditorPath}/${newSlug}`)
              }}
              getStepActions={getStepActions}
              getItemActions={getItemActions}
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
                  getItemActions={(item, itemIndex) => getItemActions(currentStep, stepIndex, item, itemIndex)}
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

QuizEditorSteps.propTypes = {
  addStep: T.func.isRequired,
  copyStep: T.func.isRequired,
  moveStep: T.func.isRequired,
  removeStep: T.func.isRequired,

  addItem: T.func.isRequired,
  copyItem: T.func.isRequired,
  moveItem: T.func.isRequired,
  removeItem: T.func.isRequired
}

export {
  QuizEditorSteps
}
