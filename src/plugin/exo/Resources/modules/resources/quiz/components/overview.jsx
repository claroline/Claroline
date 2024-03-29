import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {displayDuration, displayDate} from '#/main/app/intl/date'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'
import {ResourceOverview} from '#/main/core/resource/components/overview'

import {correctionModes, markModes, SHOW_CORRECTION_AT_DATE, SHOW_SCORE_AT_NEVER} from '#/plugin/exo/quiz/enums'
import {AttemptsChart} from '#/plugin/exo/charts/attempts/components/chart'

// TODO : show info about number of attempts

const Parameters = props =>
  <ul className="exercise-parameters">
    <li className="exercise-parameter">
      <span className="fa fa-fw fa-check-square icon-with-text-right" />
      {trans('results_availability', {}, 'quiz')} :
      &nbsp;
      <b>
        {props.showCorrectionAt === SHOW_CORRECTION_AT_DATE ?
          displayDate(props.correctionDate, false, true) :
          trans(correctionModes.find(mode => mode[0] === props.showCorrectionAt)[1], {}, 'quiz')
        }
      </b>
    </li>

    <li className="exercise-parameter">
      <span className="fa fa-fw fa-percent icon-with-text-right" />
      {trans('score_availability', {}, 'quiz')} :
      &nbsp;
      <b>{trans(markModes.find(mode => mode[0] === props.showScoreAt)[1], {}, 'quiz')}</b>
    </li>

    <li className="exercise-parameter">
      <span className="fa fa-fw fa-sign-out icon-with-text-right" />
      {trans('test_exit', {}, 'quiz')} :
      &nbsp;
      <b>{props.interruptible ? trans('yes') : trans('no')}</b>
    </li>

    <li className="exercise-parameter">
      <span className="fa fa-fw fa-refresh icon-with-text-right" />
      {trans('maximum_attempts', {}, 'quiz')} :
      &nbsp;
      <b>{props.maxAttempts ? props.maxAttempts : '-'}</b>
    </li>

    {(props.timeLimited && props.duration) &&
      <li className="exercise-parameter">
        <span className="fa fa-fw fa-clock icon-with-text-right" />
        {trans('duration')} :
        &nbsp;
        <b>{displayDuration(props.duration)}</b>
      </li>
    }
  </ul>

Parameters.propTypes = {
  showCorrectionAt: T.string.isRequired,
  correctionDate: T.string,
  showScoreAt: T.string.isRequired,
  interruptible: T.bool.isRequired,
  maxAttempts: T.number,
  timeLimited: T.bool.isRequired,
  duration: T.number
}

Parameters.defaultProps = {
  timeLimited: false
}

const QuizOverview = props => {
  let maxAttemptsReached = false
  if (get(props.quiz, 'parameters.maxAttempts')) {
    maxAttemptsReached = get(props.userEvaluation, 'nbAttempts', 0) >= get(props.quiz, 'parameters.maxAttempts')
  }

  return (
    <ResourceOverview
      contentText={props.quiz.description}
      evaluation={props.userEvaluation}
      resourceNode={props.resourceNode}
      display={{
        score: props.quiz.parameters.showScoreAt !== SHOW_SCORE_AT_NEVER,
        scoreMax: get(props.quiz, 'score.total'),
        successScore: get(props.quiz, 'parameters.successScore'),
        feedback: !!props.quiz.parameters.successMessage || !!props.quiz.parameters.failureMessage
      }}
      feedbacks={{
        success: props.quiz.parameters.successMessage,
        failure: props.quiz.parameters.failureMessage,
        closed: maxAttemptsReached ? [[
          trans('exercise_attempt_limit', {}, 'quiz'),
          props.quiz.parameters.attemptsReachedMessage
        ]] : undefined
      }}
      statusTexts={{
        opened: trans('exercise_status_opened_message', {}, 'quiz'),
        completed: trans('exercise_status_completed_message', {}, 'quiz'),
        passed: trans('exercise_status_passed_message', {}, 'quiz'),
        failed: trans('exercise_status_failed_message', {}, 'quiz')
      }}
      details={[
        [trans('attempts'), get(props.userEvaluation, 'nbAttempts', 0) + (get(props.quiz, 'parameters.maxAttempts') ? ' / ' + get(props.quiz, 'parameters.maxAttempts') : '') ]
      ]}

      actions={[
        {
          type: LINK_BUTTON,
          label: trans('start', {}, 'actions'),
          target: `${props.path}/play`,
          primary: true,
          disabled: props.empty || (maxAttemptsReached && !props.editable),
          disabledMessages: [
            props.empty && trans('start_disabled_empty', {}, 'quiz')
          ].filter(value => !!value)
        }, {
          type: LINK_BUTTON,
          label: trans('test', {}, 'actions'),
          displayed: props.editable && !props.empty,
          target: `${props.path}/test`
        }
      ]}
    >
      {props.quiz.parameters.showMetadata &&
        <section className="resource-parameters mb-3">
          <h3 className="h2">{trans('configuration')}</h3>

          <Parameters
            showCorrectionAt={props.quiz.parameters.showCorrectionAt}
            correctionDate={props.quiz.parameters.correctionDate}
            showScoreAt={props.quiz.parameters.showScoreAt}
            interruptible={props.quiz.parameters.interruptible}
            maxAttempts={props.quiz.parameters.maxAttempts}
            timeLimited={props.quiz.parameters.timeLimited}
            duration={props.quiz.parameters.duration}
          />
        </section>
      }

      {props.showStats && ['user', 'both'].includes(get(props.quiz, 'parameters.overviewStats')) &&
        <AttemptsChart
          quizId={props.quiz.id}
          userId={props.currentUserId}
          steps={props.quiz.steps}
          questionNumberingType={get(props.quiz, 'parameters.questionNumbering')}
        />
      }

      {props.showStats && ['all', 'both'].includes(get(props.quiz, 'parameters.overviewStats')) &&
        <AttemptsChart
          quizId={props.quiz.id}
          steps={props.quiz.steps}
          questionNumberingType={get(props.quiz, 'parameters.questionNumbering')}
        />
      }
    </ResourceOverview>
  )
}

QuizOverview.propTypes = {
  path: T.string.isRequired,
  empty: T.bool.isRequired,
  editable: T.bool.isRequired,
  quiz: T.shape({
    id: T.string.isRequired,
    description: T.string,
    parameters: T.object.isRequired,
    picking: T.object.isRequired,
    steps: T.array
  }).isRequired,
  userEvaluation: T.shape(
    ResourceEvaluationTypes.propTypes
  ),
  resourceNode: T.object,
  currentUserId: T.string,
  showStats: T.bool.isRequired
}

export {
  QuizOverview
}
