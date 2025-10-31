import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import cloneDeep from 'lodash/cloneDeep'
import omit from 'lodash/omit'
import set from 'lodash/set'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {EditorPage} from '#/main/app/editor'
import {ChoiceInput} from '#/main/app/data/types/choice/components/input'
import {NumberInput} from '#/main/app/data/types/number/components/input'

import {QuizType} from '#/plugin/exo/resources/quiz/components/type'
import {constants} from '#/plugin/exo/resources/quiz/constants'
import {configureTypeEditor, setTypePresets} from '#/plugin/exo/resources/quiz/types'

import ScoreNone from '#/plugin/exo/scores/none'
import ScoreSum from '#/plugin/exo/scores/sum'
import {Step} from '#/plugin/exo/resources/quiz/prop-types'

import {chainSync, gtZero, notBlank, number} from '#/main/app/data/types/validators'

const hasEnd = (quiz) => get(quiz, 'parameters.showEndPage')

const supportedScores = [
  ScoreNone,
  ScoreSum
]

const QuizEditorParameters = props => {
  const currentScore = supportedScores.find(score => score.name === get(props.score, 'type'))
  const availableScores = supportedScores.reduce((scoreChoices, current) => Object.assign(scoreChoices, {
    [current.name]: current.meta.label
  }), {})

  return (
    <EditorPage
      title={trans('parameters')}
      dataPart="resource"
      definition={configureTypeEditor(props.quizType, [
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'parameters.type',
              label: trans('type'),
              type: 'string',
              required: true,
              hideLabel: true,
              render: (quiz) => (
                <QuizType
                  type={get(quiz, 'parameters.type')}
                  selectAction={(type) => ({
                    type: CALLBACK_BUTTON,
                    callback: () => {
                      props.update(setTypePresets(type, quiz))
                    },
                    confirm: {
                      message: trans('change_quiz_type_message', {}, 'quiz'),
                      button: trans('change', {}, 'actions')
                    }
                  })}
                />
              )
            }, {
              name: 'parameters.hasExpectedAnswers',
              label: trans('has_expected_answers', {}, 'quiz'),
              type: 'boolean',
              help: [
                trans('has_expected_answers_help', {}, 'quiz'),
                trans('has_expected_answers_help_score', {}, 'quiz')
              ],
              onChange: (value) => {
                if (!value) {
                  // we need to change score rule
                  props.updateProp('score.type', ScoreNone.name)

                  // we need to disable expected answers on items
                  const newSteps = cloneDeep(props.steps)
                  newSteps.map(step => {
                    step.items.map(item => {
                      item.hasExpectedAnswers = false
                      item.score = {type: ScoreNone.name}
                    })
                  })

                  props.updateProp('steps', newSteps)
                }
              }
            }
          ]
        }, {
          icon: 'fa fa-fw fa-dice',
          title: trans('attempts_pick', {}, 'quiz'),
          primary: true,
          fields: [
            {
              name: 'parameters.mandatoryQuestions',
              label: trans('make_questions_mandatory', {}, 'quiz'),
              type: 'boolean'
            }, {
              name: 'picking.type',
              label: trans('quiz_picking_type', {}, 'quiz'),
              type: 'choice',
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: constants.QUIZ_PICKINGS
              },
              onChange: (pickingType) => {
                if (constants.QUIZ_PICKING_TAGS === pickingType) {
                  props.updateProp('picking.randomPick', constants.SHUFFLE_ALWAYS)
                  props.updateProp('picking.pick', [])
                } else {
                  props.updateProp('picking.pick', 0)
                }
              },
              linked: [
                // Standard picking
                {
                  name: 'picking.randomPick',
                  label: trans('random_picking', {}, 'quiz'),
                  type: 'choice',
                  displayed: (quiz) => constants.QUIZ_PICKING_DEFAULT === get(quiz, 'picking.type'),
                  required: true,
                  options: {
                    inline: true,
                    condensed: false,
                    choices: constants.SHUFFLE_MODES
                  },
                  linked: [
                    {
                      name: 'picking.pick',
                      label: trans('number_steps_draw', {}, 'quiz'),
                      help: trans('number_steps_draw_help', {}, 'quiz'),
                      type: 'number',
                      displayed: (quiz) => constants.SHUFFLE_NEVER !== get(quiz, 'picking.randomPick'),
                      required: true,
                      options: {
                        min: 0
                      }
                    }
                  ]
                },

                // Tag picking
                {
                  name: 'picking.randomPick',
                  label: trans('random_picking', {}, 'quiz'),
                  type: 'choice',
                  displayed: (quiz) => constants.QUIZ_PICKING_TAGS === get(quiz, 'picking.type'),
                  required: true,
                  options: {
                    inline: true,
                    condensed: false,
                    choices: omit(constants.SHUFFLE_MODES, constants.SHUFFLE_NEVER)
                  },
                  linked: [
                    {
                      name: 'picking.pageSize',
                      label: trans('number_question_page', {}, 'quiz'),
                      type: 'number',
                      required: true,
                      options: {
                        min: 1
                      }
                    }, {
                      name: 'picking.pick',
                      label: trans('tags_to_pick', {}, 'quiz'),
                      help: trans('picking_tag_input_help', {}, 'quiz'),
                      type: 'collection',
                      required: true,
                      options: {
                        placeholder: trans('no_picked_tag', {}, 'quiz'),
                        button: trans('add_tag', {}, 'actions'),
                        render: (pickedTag = {}, pickedTagErrors, pickedTagIndex) => (
                          <div className="tag-control">
                            <ChoiceInput
                              id={`picking-pick-tag-${pickedTagIndex}`}
                              multiple={false}
                              noEmpty={false}
                              condensed={true}
                              placeholder={trans('quiz_select_picking_tags', {}, 'quiz')}
                              choices={props.tags.reduce((acc, current) => Object.assign(acc, {
                                [current]: current
                              }), {})}
                              value={pickedTag[0]}
                              onChange={value => {
                                const newErrors = props.errors ? cloneDeep(props.errors) : {}
                                set(newErrors, `resource.picking.pick[${pickedTagIndex}]`, notBlank(value))
                                props.setErrors(newErrors)

                                props.updateProp(`picking.pick[${pickedTagIndex}][0]`, value)
                              }}
                            />

                            <NumberInput
                              id={`picking-pick-count-${pickedTagIndex}`}
                              min={1}
                              value={pickedTag[1]}
                              onChange={value => {
                                const newErrors = props.errors ? cloneDeep(props.errors) : {}
                                set(newErrors, `resource.picking.pick[${pickedTagIndex}]`, chainSync(value, {}, [notBlank, number, gtZero]))
                                props.setErrors(newErrors)

                                props.updateProp(`picking.pick[${pickedTagIndex}][1]`, value)
                              }}
                            />
                          </div>
                        )
                      }
                    }
                  ]
                },

                {
                  name: 'picking.randomOrder',
                  label: trans('random_order', {}, 'quiz'),
                  type: 'choice',
                  required: true,
                  options: {
                    inline: true,
                    condensed: false,
                    choices: constants.SHUFFLE_ALWAYS !== props.randomPick ?
                      constants.SHUFFLE_MODES
                      :
                      omit(constants.SHUFFLE_MODES, constants.SHUFFLE_ONCE)
                  }
                }
              ]
            }
          ]
        }, {
          icon: ' fa fa-fw fa-play',
          title: trans('attempts_play', {}, 'quiz'),
          primary: true,
          fields: [
            {
              name: 'parameters.progressionDisplayed',
              type: 'boolean',
              label: trans('show_progression_gauge', {}, 'quiz')
            }, {
              name: 'parameters.timeLimited',
              label: trans('limit_quiz_duration', {}, 'quiz'),
              type: 'boolean',
              calculated: (quiz) => 0 < get(quiz, 'parameters.duration') || get(quiz, 'parameters.timeLimited'),
              onChange: (checked) => {
                if (!checked) {
                  props.updateProp('parameters.duration', 0)
                } else {
                  props.updateProp('parameters.duration', null) // to force user to fill the field
                  props.updateProp('parameters.interruptible', false)
                }
              },
              linked: [
                {
                  name: 'parameters.duration',
                  label: trans('duration'),
                  type: 'time',
                  displayed: (quiz) => 0 < get(quiz, 'parameters.duration') || get(quiz, 'parameters.timeLimited'),
                  required: true
                }
              ]
            }, {
              name: 'parameters.showFeedback',
              label: trans('show_feedback', {}, 'quiz'),
              type: 'boolean',
              displayed: (quiz) => get(quiz, 'parameters.hasExpectedAnswers'),
              onChange: (value) => {
                if (value) {
                  props.updateProp('parameters.answersEditable', false)
                }
              }
              // TODO : add help text
            }, {
              name: 'parameters.showBack',
              label: trans('show_back', {}, 'quiz'),
              type: 'boolean'
            }, {
              name: 'parameters.answersEditable',
              label: trans('allow_to_edit_answers', {}, 'quiz'),
              type: 'boolean',
              disabled: (quiz) => get(quiz, 'parameters.hasExpectedAnswers') && get(quiz, 'parameters.showFeedback')
              // TODO : add help text
            }, {
              name: 'parameters.interruptible',
              label: trans('allow_test_exit', {}, 'quiz'),
              type: 'boolean',
              disabled: (quiz) => get(quiz, 'parameters.timeLimited') || 0 < get(quiz, 'parameters.duration')
              // TODO : add help text
            }, {
              name: 'parameters.showEndConfirm',
              label: trans('show_end_confirm', {}, 'quiz'),
              help: trans('show_end_confirm_help', {}, 'quiz'),
              type: 'boolean'
            }
          ]
        }, {
          icon: 'fa fa-fw fa-flag-checkered',
          title: trans('end_page'),
          primary: true,
          fields: [
            {
              name: 'parameters.showEndPage',
              type: 'boolean',
              label: trans('show_end_page'),
              linked: [
                {
                  name: 'parameters.endMessage',
                  type: 'html',
                  label: trans('end_message'),
                  displayed: hasEnd,
                  options: {
                    workspace: props.workspace
                  }
                }, {
                  name: 'parameters.intermediateScores',
                  type: 'choice',
                  label: trans('show_intermediate_scores', {}, 'quiz'),
                  displayed: hasEnd,
                  options: {
                    choices: {
                      none: trans('none'),
                      step: trans('per_step', {}, 'quiz'),
                      tag: trans('per_tag', {}, 'quiz')
                    }
                  }
                }, {
                  name: 'parameters.endNavigation',
                  type: 'boolean',
                  label: trans('resource_end_navigation', {}, 'resource'),
                  help: trans('show_end_navigation_help', {}, 'quiz'),
                  displayed: hasEnd,
                  linked: [
                    {
                      name: 'parameters.back._enabled',
                      type: 'boolean',
                      label: trans('resource_end_back', {}, 'resource'),
                      displayed: (quiz) => hasEnd(quiz) && get(quiz, 'parameters.endNavigation'),
                      calculated: (quiz) => !!get(quiz, 'parameters.back.type') || get(quiz, 'parameters.back._enabled'),
                      onChange: (enabled) => {
                        if (!enabled) {
                          props.updateProp('parameters.back.type', null)
                          props.updateProp('parameters.back.label', null)
                          props.updateProp('parameters.back.target', null)
                        }
                      },
                      linked: [
                        {
                          name: 'parameters.back.label',
                          type: 'string',
                          label: trans('resource_end_back_label', {}, 'resource'),
                          placeholder: trans('return-home', {}, 'actions'),
                          displayed: (quiz) => hasEnd(quiz) && get(quiz, 'parameters.endNavigation') && (!!get(quiz, 'parameters.back.type') || get(quiz, 'parameters.back._enabled'))
                        }, {
                          name: 'parameters.back.type',
                          displayed: (quiz) => hasEnd(quiz) && get(quiz, 'parameters.endNavigation') && (!!get(quiz, 'parameters.back.type') || get(quiz, 'parameters.back._enabled')),
                          label: trans('resource_end_back_type', {}, 'resource'),
                          type: 'choice',
                          required: true,
                          options: {
                            choices: {
                              workspace: trans('resource_end_back_workspace', {}, 'resource'),
                              desktop: trans('resource_end_back_desktop', {}, 'resource'),
                              resource: trans('resource_end_back_resource', {}, 'resource')
                            }
                          },
                          linked: [
                            {
                              name: 'parameters.back.target',
                              type: 'resource',
                              required: true,
                              label: trans('resource'),
                              displayed: (quiz) => hasEnd(quiz) && get(quiz, 'parameters.endNavigation') && 'resource' === get(quiz, 'parameters.back.type')
                            }
                          ]
                        }
                      ]
                    }
                  ]
                }, {
                  name: 'parameters._showEndStats',
                  type: 'boolean',
                  label: trans('show_attempts_stats', {}, 'quiz'),
                  displayed: (quiz) => false && get(quiz, 'parameters.hasExpectedAnswers') && hasEnd(quiz),
                  calculated: (quiz) => 'none' !== get(quiz, 'parameters.endStats'),
                  onChange: (checked) => {
                    if (checked) {
                      props.updateProp('parameters.endStats', 'user')
                    } else {
                      props.updateProp('parameters.endStats', 'none')
                    }
                  },
                  linked: [
                    {
                      name: 'parameters.endStats',
                      type: 'choice',
                      label: trans('calculation_mode', {}, 'quiz'),
                      hideLabel: true,
                      displayed: (quiz) => 'none' !== get(quiz, 'parameters.endStats'),
                      options: {
                        choices: {
                          user: trans('user'),
                          all: trans('all'),
                          both: trans('both')
                        }
                      }
                    }
                  ]
                }
              ]
            }
          ]
        }, {
          icon: 'fa fa-fw fa-check-double',
          title: trans('results', {}, 'quiz'),
          primary: true,
          fields: [
            {
              name: 'parameters.anonymizeAttempts',
              label: trans('anonymize_results', {}, 'quiz'),
              type: 'boolean'
            }, {
              name: 'parameters.showCorrectionAt',
              label: trans('results_availability', {}, 'quiz'),
              type: 'choice',
              required: true,
              options: {
                condensed: true,
                noEmpty: true,
                choices: constants.QUIZ_RESULTS_AVAILABILITY
              },
              onChange: (quizResults) => {
                if (constants.QUIZ_RESULTS_AT_DATE !== quizResults) {
                  props.updateProp('parameters.correctionDate', null)
                }
              },
              linked: [
                {
                  name: 'parameters.correctionDate',
                  label: trans('access_date'),
                  type: 'date',
                  required: true,
                  displayed: (quiz) => constants.QUIZ_RESULTS_AT_DATE === get(quiz, 'parameters.showCorrectionAt'),
                  options: {
                    time: true
                  }
                }
              ]
            }, {
              name: 'parameters.showFullCorrection',
              label: trans('show_expected_answers', {}, 'quiz'),
              displayed: (quiz) => get(quiz, 'parameters.hasExpectedAnswers'),
              type: 'boolean'
            }, {
              name: 'parameters.showStatistics',
              label: trans('enable_statistics'),
              type: 'boolean',
              linked: [
                {
                  name: 'parameters.allPapersStatistics',
                  label: trans('calculation_mode', {}, 'quiz'),
                  displayed: (quiz) => get(quiz, 'parameters.showStatistics'),
                  type: 'choice',
                  required: true,
                  options: {
                    noEmpty: true,
                    condensed: true,
                    choices: {
                      all: trans('statistics_all_attempts', {}, 'quiz'),
                      finished: trans('statistics_finished_attempts', {}, 'quiz')
                    }
                  },
                  calculated: (quiz) => get(quiz, 'parameters.allPapersStatistics') ? 'all' : 'finished',
                  onChange: (mode) => {
                    if ('all' === mode) {
                      props.updateProp('parameters.allPapersStatistics', true)
                    } else {
                      props.updateProp('parameters.allPapersStatistics', false)
                    }
                  }
                }
              ]
            }
          ]
        }, {
          icon: 'fa fa-fw fa-percentage',
          title: trans('score'),
          primary: true,
          displayed: (quiz) => get(quiz, 'parameters.hasExpectedAnswers'),
          fields: [
            {
              name: 'parameters.showScoreAt',
              label: trans('score_availability', {}, 'quiz'),
              type: 'choice',
              required: true,
              options: {
                condensed: true,
                noEmpty: true,
                choices: constants.QUIZ_SCORE_AVAILABILITY
              }
            }, {
              name: 'score.type',
              label: trans('calculation_mode', {}, 'quiz'),
              type: 'choice',
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: availableScores
              },
              // TODO : make it a new dataType (duplicated in item editor)
              linked: currentScore ? currentScore
                // generate the list of fields for the score type
                .configure(props.score, (prop, value) => props.updateProp(`score.${prop}`, value))
                .map(scoreProp => Object.assign({}, scoreProp, {
                  name: `score.${scoreProp.name}`,
                  // slightly ugly because I only support 1 level
                  linked: scoreProp.linked ? scoreProp.linked.map(linkedProp => Object.assign({}, linkedProp, {
                    name: `score.${linkedProp.name}`
                  })) : []
                })) : [],
              onChange: (scoreType) => {
                if (ScoreNone.name === scoreType) {
                  // we need to change score on items
                  const newSteps = cloneDeep(props.steps)
                  newSteps.map(step => {
                    step.items.map(item => {
                      item.score = {type: ScoreNone.name}
                    })
                  })

                  props.updateProp('steps', newSteps)
                }
              }
            }
          ]
        }, {
          icon: 'fa fa-fw fa-award',
          title: trans('evaluation'),
          primary: true,
          displayed: (quiz) => get(quiz, 'parameters.hasExpectedAnswers'),
          fields: [
            {
              name: 'parameters.successScore',
              label: trans('score_to_pass'),
              type: 'number',
              options: {
                min: 0,
                max: 100,
                unit: '%'
              },
              linked: [
                {
                  name: 'parameters.successMessage',
                  label: trans('success_message'),
                  type: 'html',
                  displayed: (quiz) => !!get(quiz, 'parameters.successScore'),
                  options: {
                    workspace: props.workspace
                  }
                }, {
                  name: 'parameters.failureMessage',
                  label: trans('failure_message'),
                  type: 'html',
                  displayed: (quiz) => !!get(quiz, 'parameters.successScore'),
                  options: {
                    workspace: props.workspace
                  }
                }
              ]
            }
          ]
        }, {
          icon: 'fa fa-fw fa-key',
          title: trans('access_restrictions'),
          primary: true,
          fields: [
            {
              name: 'parameters._maxAttempts',
              label: trans('restrict_user_attempts', {}, 'quiz'),
              help: trans('restrict_user_attempts_help', {}, 'quiz'),
              type: 'boolean',
              calculated: (quiz) => get(quiz, 'parameters._maxAttempts') || 0 < get(quiz, 'parameters.maxAttempts'),
              onChange: (restrict) => {
                if (restrict) {
                  props.updateProp('parameters.maxAttempts', null)
                } else {
                  props.updateProp('parameters.maxAttempts', 0)
                }
              },
              linked: [
                {
                  name: 'parameters.maxAttempts',
                  label: trans('attempts_count', {}, 'quiz'),
                  type: 'number',
                  required: true,
                  displayed: (quiz) => get(quiz, 'parameters._maxAttempts') || 0 < get(quiz, 'parameters.maxAttempts'),
                  options: {
                    min: 0
                  }
                }, {
                  name: 'parameters.attemptsReachedMessage',
                  label: trans('message'),
                  type: 'html',
                  displayed: (quiz) => get(quiz, 'parameters._maxAttempts') || 0 < get(quiz, 'parameters.maxAttempts'),
                  options: {
                    workspace: props.workspace
                  }
                }
              ]
            }
          ]
        }
      ])}
    />
  )
}

QuizEditorParameters.propTypes = {
  errors: T.object,
  quizType: T.string.isRequired,
  score: T.shape({
    type: T.string.isRequired
  }),
  steps: T.arrayOf(T.shape(
    Step.propTypes
  )),
  numberingType: T.string,
  randomPick: T.string,
  tags: T.array.isRequired,
  workspace: T.object,
  update: T.func.isRequired,
  updateProp: T.func.isRequired,
  setErrors: T.func.isRequired
}

export {
  QuizEditorParameters
}
