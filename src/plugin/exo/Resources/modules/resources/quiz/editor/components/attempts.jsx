import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {EditorPage} from '#/main/app/editor'
import {ChoiceInput} from '#/main/app/data/types/choice/components/input'
import {NumberInput} from '#/main/app/data/types/number/components/input'

import {constants} from '#/plugin/exo/resources/quiz/constants'
import {configureTypeEditor} from '#/plugin/exo/resources/quiz/types'

const QuizEditorParameters = props => {

  return (
    <EditorPage
      title={trans('attempts', {}, 'quiz')}
      dataPart="resource"
      definition={configureTypeEditor(props.quizType, [
        {
          icon: 'fa fa-fw fa-dice',
          title: trans('attempts_pick', {}, 'quiz'),
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
  QuizEditorParameters
}
