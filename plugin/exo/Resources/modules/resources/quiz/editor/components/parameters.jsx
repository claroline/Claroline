import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

import {QuizType} from '#/plugin/exo/resources/quiz/components/type'
import {constants} from '#/plugin/exo/resources/quiz/constants'

const hasOverview = (quiz) => get(quiz, 'parameters.showOverview')
const hasEnd  = (quiz) => get(quiz, 'parameters.showEndPage')

const EditorParameters = props =>
  <Fragment>
    <h3 className="h2 step-title">
      {constants.NUMBERING_NONE !== props.numbering &&
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
              render: (quiz) => (
                <QuizType
                  type={get(quiz, 'parameters.type')}
                  onChange={(type) => props.update('parameters.type', type)}
                />
              )
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
          icon: ' fa fa-fw fa-play',
          title: trans('attempts', {}, 'quiz'),
          fields: [
            {
              name: 'parameters.progressionDisplayed',
              type: 'boolean',
              label: trans('show_progression_gauge', {}, 'quiz')
            }, {
              name: 'parameters._hasDuration',
              label: trans('limit_quiz_duration', {}, 'quiz'),
              type: 'boolean',
              calculated: (quiz) => get(quiz, 'parameters.duration') || get(quiz, 'parameters._hasDuration'),
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
                  displayed: (quiz) => get(quiz, 'parameters.duration') || get(quiz, 'parameters._hasDuration'),
                  required: true
                }
              ]
            }, {
              name: 'parameters.mandatoryQuestions',
              label: trans('make_questions_mandatory', {}, 'quiz'),
              type: 'boolean'
              // TODO : add help text
            }, {
              name: 'parameters.showFeedback',
              label: trans('show_feedback', {}, 'quiz'),
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
              name: 'parameters.hasExpectedAnswers',
              label: trans('has_expected_answers', {}, 'quiz'),
              type: 'boolean',
              linked: [
                {
                  name: 'parameters.showFullCorrection',
                  label: trans('show_expected_answers', {}, 'quiz'),
                  displayed: (quiz) => get(quiz, 'parameters.hasExpectedAnswers'),
                  type: 'boolean'
                }
              ]
            }, {
              name: 'parameters.showStatistics',
              label: trans('statistics', {}, 'quiz'),
              type: 'boolean',
              linked: [
                {
                  name: 'parameters.allPapersStatistics',
                  label: trans('statistics_options', {}, 'quiz'),
                  displayed: (quiz) => get(quiz, 'parameters.showStatistics'),
                  type: 'choice',
                  options: {
                    condensed: true,
                  }
                }
              ]
            }
          ]
        }, {
          icon: 'fa fa-fw fa-key',
          title: trans('access_restrictions'),
          fields: [
            // TODO : add checkboxes
            {
              name: 'parameters.maxAttempts',
              label: trans('maximum_attempts', {}, 'quiz'),
              type: 'number',
              options: {
                min: 0
              }
            }, {
              name: 'parameters.maxAttemptsPerDay',
              label: trans('maximum_attempts_per_day', {}, 'quiz'),
              type: 'number',
              options: {
                min: 0
              }
            }, {
              name: 'parameters.maxPapers',
              label: trans('maximum_papers', {}, 'quiz'),
              type: 'number',
              options: {
                min: 0
              }
            }
          ]
        }
      ]}
    />
  </Fragment>

EditorParameters.propTypes = {
  formName: T.string.isRequired,
  numbering: T.string.isRequired,
  workspace: T.object,
  update: T.func.isRequired
}

export {
  EditorParameters
}
