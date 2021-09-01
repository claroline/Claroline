import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import cloneDeep from 'lodash/cloneDeep'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentTitle} from '#/main/app/content/components/title'
import {FormData} from '#/main/app/content/form/containers/data'
import {ChoiceInput} from '#/main/app/data/types/choice/components/input'
import {NumberInput} from '#/main/app/data/types/number/components/input'

import {QuizType} from '#/plugin/exo/resources/quiz/components/type'
import {constants} from '#/plugin/exo/resources/quiz/constants'
import {QUIZ_TYPES, configureTypeEditor, setTypePresets} from '#/plugin/exo/resources/quiz/types'

import ScoreNone from '#/plugin/exo/scores/none'
import ScoreSum from '#/plugin/exo/scores/sum'

const hasOverview = (quiz) => get(quiz, 'parameters.showOverview')
const hasEnd = (quiz) => get(quiz, 'parameters.showEndPage')

const supportedScores = [
  ScoreNone,
  ScoreSum
]

const EditorParameters = props => {
  const currentScore = supportedScores.find(score => score.name === get(props.score, 'type'))
  const availableScores = supportedScores.reduce((scoreChoices, current) => Object.assign(scoreChoices, {
    [current.name]: current.meta.label
  }), {})

  return (
    <Fragment>
      <ContentTitle
        level={3}
        displayLevel={2}
        numbering={constants.NUMBERING_NONE !== props.numberingType ? <span className="fa fa-cog" /> : undefined}
        title={trans('parameters')}
      />

      <FormData
        level={3}
        displayLevel={2}
        embedded={true}
        name={props.formName}
        sections={configureTypeEditor(props.quizType, [
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'parameters.type',
                label: trans('type'),
                type: 'string',
                required: true,
                render: (quiz) => {
                  const CurrentType = (
                    <QuizType
                      type={get(quiz, 'parameters.type')}
                      selectAction={(type) => ({
                        type: CALLBACK_BUTTON,
                        callback: () => props.update(null, setTypePresets(type, quiz)),
                        confirm: {
                          icon: 'fa fa-fw fa-warning',
                          title: trans('change_quiz_type_confirm', {}, 'quiz'),
                          subtitle: get(QUIZ_TYPES[get(quiz, 'parameters.type')], 'meta.label') + ' > ' + get(QUIZ_TYPES[type], 'meta.label'),
                          message: trans('change_quiz_type_message', {}, 'quiz'),
                          button: trans('change', {}, 'actions')
                        }
                      })}
                    />
                  )

                  return CurrentType
                }
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
                    props.update('score.type', ScoreNone.name)

                    // we need to disable expected answers on items
                    const newSteps = cloneDeep(props.steps)
                    newSteps.map(step => {
                      step.items.map(item => {
                        item.hasExpectedAnswers = false
                        item.score = {type: ScoreNone.name}
                      })
                    })

                    props.update('steps', newSteps)
                  }
                }
              }
            ]
          }, {
            icon: 'fa fa-fw fa-home',
            title: trans('overview'),
            fields: [
              {
                name: 'parameters.showOverview',
                type: 'boolean',
                label: trans('enable_overview'),
                // TODO : add message if false and there is a timer on the quiz
                linked: [
                  {
                    name: 'description',
                    type: 'html',
                    label: trans('overview_message'),
                    displayed: hasOverview,
                    options: {
                      workspace: props.workspace
                    }
                  }, {
                    name: 'parameters.showMetadata',
                    type: 'boolean',
                    label: trans('metadata_visible', {}, 'quiz'),
                    displayed: hasOverview
                  }, {
                    name: 'parameters._showOverviewStats',
                    type: 'boolean',
                    label: trans('show_attempts_stats', {}, 'quiz'),
                    displayed: (quiz) => get(quiz, 'parameters.hasExpectedAnswers'),
                    calculated: (quiz) => 'none' !== get(quiz, 'parameters.overviewStats'),
                    onChange: (checked) => {
                      if (checked) {
                        props.update('parameters.overviewStats', 'user')
                      } else {
                        props.update('parameters.overviewStats', 'none')
                      }
                    },
                    linked: [
                      {
                        name: 'parameters.overviewStats',
                        type: 'choice',
                        label: trans('calculation_mode', {}, 'quiz'),
                        hideLabel: true,
                        displayed: (quiz) => 'none' !== get(quiz, 'parameters.overviewStats'),
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
            icon: 'fa fa-fw fa-desktop',
            title: trans('display_parameters'),
            fields: [
              {
                name: 'parameters.showTitles',
                type: 'boolean',
                label: trans('show_step_titles', {}, 'quiz'),
                linked: [
                  {
                    name: 'parameters.numbering',
                    type: 'choice',
                    label: trans('quiz_numbering', {}, 'quiz'),
                    required: true,
                    displayed: (quiz) => get(quiz, 'parameters.showTitles', false),
                    options: {
                      noEmpty: true,
                      condensed: true,
                      choices: constants.QUIZ_NUMBERINGS
                    }
                  }
                ]
              }, {
                name: 'parameters.showQuestionTitles',
                type: 'boolean',
                label: trans('show_question_titles', {}, 'quiz'),
                linked: [
                  {
                    name: 'parameters.questionNumbering',
                    type: 'choice',
                    label: trans('quiz_question_numbering', {}, 'quiz'),
                    required: true,
                    displayed: (quiz) => get(quiz, 'parameters.showQuestionTitles', false),
                    options: {
                      noEmpty: true,
                      condensed: true,
                      choices: constants.QUIZ_NUMBERINGS
                    }
                  }
                ]
              }
            ]
          }, {
            icon: 'fa fa-fw fa-dice',
            title: trans('attempts_pick', {}, 'quiz'),
            fields: [
              {
                name: 'parameters.mandatoryQuestions',
                label: trans('make_questions_mandatory', {}, 'quiz'),
                type: 'boolean'
                // TODO : add help text
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
                    props.update('picking.randomPick', constants.SHUFFLE_ALWAYS)
                    props.update('picking.pick', [])
                  } else {
                    props.update('picking.pick', 0)
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
                          button: trans('add-tag', {}, 'actions'),
                          render: (pickedTag = {}, pickedTagErrors, pickedTagIndex) => {
                            const TagPicking = (
                              <div className="tag-control">
                                <ChoiceInput
                                  id={`picking-pick-tag-${pickedTagIndex}`}
                                  size="sm"
                                  multiple={false}
                                  noEmpty={false}
                                  condensed={true}
                                  placeholder={trans('quiz_select_picking_tags', {}, 'quiz')}
                                  choices={props.tags.reduce((acc, current) => Object.assign(acc, {
                                    [current]: current
                                  }), {})}
                                  value={pickedTag[0]}
                                  onChange={value => props.update(`picking.pick[${pickedTagIndex}][0]`, value)}
                                />

                                <NumberInput
                                  id={`picking-pick-count-${pickedTagIndex}`}
                                  size="sm"
                                  min={1}
                                  value={pickedTag[1]}
                                  onChange={value => props.update(`picking.pick[${pickedTagIndex}][1]`, value)}
                                />
                              </div>
                            )

                            return TagPicking
                          }
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
                    props.update('parameters.duration', 0)
                  } else {
                    props.update('parameters.duration', null) // to force user to fill the field
                    props.update('parameters.interruptible', false)
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
                    props.update('parameters.answersEditable', false)
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
                    label: trans('show_end_navigation', {}, 'quiz'),
                    help: trans('show_end_navigation_help', {}, 'quiz'),
                    displayed: hasEnd
                  }, {
                    name: 'parameters._showEndStats',
                    type: 'boolean',
                    label: trans('show_attempts_stats', {}, 'quiz'),
                    displayed: (quiz) => get(quiz, 'parameters.hasExpectedAnswers'),
                    calculated: (quiz) => 'none' !== get(quiz, 'parameters.endStats'),
                    onChange: (checked) => {
                      if (checked) {
                        props.update('parameters.endStats', 'user')
                      } else {
                        props.update('parameters.endStats', 'none')
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
                    props.update('parameters.correctionDate', null)
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
                label: trans('statistics', {}, 'quiz'),
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
                        props.update('parameters.allPapersStatistics', true)
                      } else {
                        props.update('parameters.allPapersStatistics', false)
                      }
                    }
                  }
                ]
              }
            ]
          }, {
            icon: 'fa fa-fw fa-percentage',
            title: trans('score'),
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
                  .configure(props.score, (prop, value) => props.update(`score.${prop}`, value))
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

                    props.update('steps', newSteps)
                  }
                }
              }
            ]
          }, {
            icon: 'fa fa-fw fa-award',
            title: trans('evaluation'),
            displayed: (quiz) => get(quiz, 'parameters.hasExpectedAnswers'),
            fields: [
              {
                name: 'parameters.successScore',
                label: trans('quiz_success_score', {}, 'quiz'),
                type: 'number',
                required: true,
                options: {
                  min: 0,
                  max: 100,
                  unit: '%'
                }
              }, {
                name: 'parameters.successMessage',
                label: trans('success_message'),
                type: 'html'
              }, {
                name: 'parameters.failureMessage',
                label: trans('failure_message'),
                type: 'html'
              }
            ]
          }, {
            icon: 'fa fa-fw fa-key',
            title: trans('access_restrictions'),
            fields: [
              {
                name: 'parameters._maxAttempts',
                label: trans('restrict_user_attempts', {}, 'quiz'),
                help: trans('restrict_user_attempts_help', {}, 'quiz'),
                type: 'boolean',
                calculated: (quiz) => get(quiz, 'parameters._maxAttempts') || 0 < get(quiz, 'parameters.maxAttempts'),
                onChange: (restrict) => {
                  if (restrict) {
                    props.update('parameters.maxAttempts', null)
                  } else {
                    props.update('parameters.maxAttempts', 0)
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
                  }
                ]
              }, {
                name: 'parameters._maxAttemptsPerDay',
                label: trans('restrict_user_attempts_per_day', {}, 'quiz'),
                help: trans('restrict_user_attempts_per_day_help', {}, 'quiz'),
                type: 'boolean',
                calculated: (quiz) => get(quiz, 'parameters._maxAttemptsPerDay') || 0 < get(quiz, 'parameters.maxAttemptsPerDay'),
                onChange: (restrict) => {
                  if (restrict) {
                    props.update('parameters.maxAttemptsPerDay', null)
                  } else {
                    props.update('parameters.maxAttemptsPerDay', 0)
                  }
                },
                linked: [
                  {
                    name: 'parameters.maxAttemptsPerDay',
                    label: trans('attempts_count', {}, 'quiz'),
                    type: 'number',
                    required: true,
                    displayed: (quiz) => get(quiz, 'parameters._maxAttemptsPerDay') || 0 < get(quiz, 'parameters.maxAttemptsPerDay'),
                    options: {
                      min: 0
                    }
                  }
                ]
              }, {
                name: 'parameters._maxPapers',
                label: trans('restrict_total_attempts', {}, 'quiz'),
                help: trans('restrict_total_attempts_help', {}, 'quiz'),
                type: 'boolean',
                calculated: (quiz) => get(quiz, 'parameters._maxPapers') || 0 < get(quiz, 'parameters.maxPapers'),
                onChange: (restrict) => {
                  if (restrict) {
                    props.update('parameters.maxPapers', null)
                  } else {
                    props.update('parameters.maxPapers', 0)
                  }
                },
                linked: [
                  {
                    name: 'parameters.maxPapers',
                    label: trans('attempts_count', {}, 'quiz'),
                    type: 'number',
                    required: true,
                    displayed: (quiz) => get(quiz, 'parameters._maxPapers') || 0 < get(quiz, 'parameters.maxPapers'),
                    options: {
                      min: 0
                    }
                  }
                ]
              }
            ]
          }
        ])}
      />
    </Fragment>
  )
}

EditorParameters.propTypes = {
  formName: T.string.isRequired,
  quizType: T.string.isRequired,
  score: T.shape({
    type: T.string.isRequired
  }),
  steps: T.arrayOf(T.shape({
    // TODO : prop types
  })),
  numberingType: T.string.isRequired,
  randomPick: T.string,
  tags: T.array.isRequired,
  workspace: T.object,
  update: T.func.isRequired
}

export {
  EditorParameters
}
