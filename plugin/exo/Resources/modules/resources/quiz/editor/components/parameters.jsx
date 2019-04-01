import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {ChoiceInput} from '#/main/app/data/types/choice/components/input'
import {NumberInput} from '#/main/app/data/types/number/components/input'

import {QuizType} from '#/plugin/exo/resources/quiz/components/type'
import {constants} from '#/plugin/exo/resources/quiz/constants'

const hasOverview = (quiz) => get(quiz, 'parameters.showOverview')
const hasEnd = (quiz) => get(quiz, 'parameters.showEndPage')

const EditorParameters = props =>
  <Fragment>
    <h3 className="h2 step-title">
      {constants.NUMBERING_NONE !== props.numberingType &&
        <span className="h-numbering">
          <span className="fa fa-cog" />
        </span>
      }

      {trans('parameters')}
    </h3>

    <FormData
      level={3}
      displayLevel={2}
      embedded={true}
      name={props.formName}
      sections={[
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
                    onChange={(type) => props.update('parameters.type', type.name)}
                  />
                )

                return CurrentType
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
                }
              ]
            }
          ]
        }, {
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          fields: [
            {
              name: 'parameters.numbering',
              type: 'choice',
              label: trans('quiz_numbering', {}, 'quiz'),
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: constants.QUIZ_NUMBERINGS
              }
            }
          ]
        }, {
          icon: 'fa fa-fw fa-dice',
          title: trans('picking', {}, 'quiz'),
          fields: [
            {
              name: 'parameters.hasExpectedAnswers',
              label: trans('has_expected_answers', {}, 'quiz'),
              type: 'boolean'
            }, {
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
                                choices={props.tags.reduce((acc, current) => Object.assign({}, {
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
          title: trans('attempts', {}, 'quiz'),
          fields: [
            {
              name: 'parameters.progressionDisplayed',
              type: 'boolean',
              label: trans('show_progression_gauge', {}, 'quiz')
            }, {
              name: 'parameters.timeLimited',
              label: trans('limit_quiz_duration', {}, 'quiz'),
              type: 'boolean',
              calculated: (quiz) => get(quiz, 'parameters.duration') || get(quiz, 'parameters.timeLimited'),
              onChange: (checked) => {
                if (!checked) {
                  props.update('parameters.duration', 0)
                } else {
                  props.update('parameters.duration', null) // to force user to fill the field
                }
              },
              linked: [
                {
                  name: 'parameters.duration',
                  label: trans('duration'),
                  type: 'time',
                  displayed: (quiz) => get(quiz, 'parameters.duration') || get(quiz, 'parameters.timeLimited'),
                  required: true
                }
              ]
            }, {
              name: 'parameters.showFeedback',
              label: trans('show_feedback', {}, 'quiz'),
              displayed: (quiz) => get(quiz, 'parameters.hasExpectedAnswers'),
              type: 'boolean'
              // TODO : add help text
            }, {
              name: 'parameters.interruptible',
              label: trans('allow_test_exit', {}, 'quiz'),
              type: 'boolean'
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
          title: trans('end_page', {}, 'quiz'),
          fields: [
            {
              name: 'parameters.showEndPage',
              type: 'boolean',
              label: trans('show_end_page', {}, 'quiz'),
              linked: [
                {
                  name: 'parameters.endMessage',
                  type: 'html',
                  label: trans('end_message', {}, 'quiz'),
                  displayed: hasEnd,
                  options: {
                    workspace: props.workspace
                  }
                }, {
                  name: 'parameters.endNavigation',
                  type: 'boolean',
                  label: trans('show_end_navigation', {}, 'quiz'),
                  help: trans('show_end_navigation_help', {}, 'quiz'),
                  displayed: hasEnd
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
            }/*{
              name: 'score.type',
              label: trans('calculation_mode', {}, 'quiz'),
              type: 'choice',
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                // get the list of score supported by the current type
                choices: availableScores
              },
              linked: currentScore ? currentScore
                // generate the list of fields for the score type
                  .configure(get(props.item, 'score'))
                  .map(scoreProp => Object.assign({}, scoreProp, {
                    name: `score.${scoreProp.name}`
                  })) : []
            }*/
          ]
        }, {
          icon: 'fa fa-fw fa-award',
          title: trans('evaluation'),
          displayed: (quiz) => get(quiz, 'parameters.hasExpectedAnswers'),
          fields: [
            {
              name: 'parameters',
              label: trans('', {}, 'quiz'),
              type: 'choice',
              options: {

              }
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
      ]}
    />
  </Fragment>

EditorParameters.propTypes = {
  formName: T.string.isRequired,
  numberingType: T.string.isRequired,
  randomPick: T.string,
  tags: T.array.isRequired,
  workspace: T.object,
  update: T.func.isRequired
}

export {
  EditorParameters
}
