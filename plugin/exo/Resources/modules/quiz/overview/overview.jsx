import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans, tex} from '#/main/core/translation'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'
import {UserEvaluation as UserEvaluationType} from '#/main/core/resource/prop-types'
import {ResourceOverview} from '#/main/core/resource/components/overview.jsx'

import {select} from '#/plugin/exo/quiz/selectors'
import {correctionModes, markModes, SHOW_CORRECTION_AT_DATE, SHOW_SCORE_AT_NEVER} from '#/plugin/exo/quiz/enums'

const Parameters = props =>
  <ul className="exercise-parameters">
    <li className="exercise-parameter">
      <span className="fa fa-fw fa-check-square-o icon-with-text-right" />
      {tex('availability_of_correction')} :
      &nbsp;
      <b>
        {props.showCorrectionAt === SHOW_CORRECTION_AT_DATE ?
          props.correctionDate :
          tex(correctionModes.find(mode => mode[0] === props.showCorrectionAt)[1])
        }
      </b>
    </li>
    <li className="exercise-parameter">
      <span className="fa fa-fw fa-percent icon-with-text-right" />
      {tex('availability_of_score')} :
      &nbsp;
      <b>{tex(markModes.find(mode => mode[0] === props.showScoreAt)[1])}</b>
    </li>
    <li className="exercise-parameter">
      <span className="fa fa-fw fa-sign-out icon-with-text-right" />
      {tex('test_exit')} :
      &nbsp;
      <b>{props.interruptible ? trans('yes') : trans('no')}</b>
    </li>
    <li className="exercise-parameter">
      <span className="fa fa-fw fa-files-o icon-with-text-right" />
      {tex('maximum_tries')} :
      &nbsp;
      <b>{props.maxAttempts ? props.maxAttempts : '-'}</b>
    </li>
    {props.timeLimited &&
      <li className="exercise-parameter">
        <span className="fa fa-fw fa-clock-o icon-with-text-right" />
        {trans('duration')} :
        &nbsp;
        <b>
          {props.duration ?
            `${props.duration} ${props.duration > 1 ? trans('minutes') : trans('minute')}` :
            '-'
          }
        </b>
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

const OverviewComponent = props =>
  <ResourceOverview
    contentText={props.quiz.description ||
      <span className="empty-text">{trans('no_description')}</span>
    }
    progression={{
      status: props.userEvaluation ? props.userEvaluation.status : undefined,
      statusTexts: {
        opened: tex('exercise_status_opened_message'),
        passed: tex('exercise_status_passed_message'),
        failed: tex('exercise_status_failed_message')
      },
      score: {
        displayed: props.quiz.parameters.showScoreAt !== SHOW_SCORE_AT_NEVER,
        current: props.userEvaluation.score,
        total: props.userEvaluation.scoreMax
      }
    }}
    actions={[
      {
        type: 'link',
        icon: 'fa fa-fw fa-play icon-with-text-right',
        label: tex('exercise_start'),
        target: '/play',
        primary: true,
        disabled: false
      }
    ]}
  >
    <section className="resource-parameters">
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
  </ResourceOverview>

OverviewComponent.propTypes = {
  empty: T.bool.isRequired,
  editable: T.bool.isRequired,
  quiz: T.shape({
    description: T.string,
    meta: T.object.isRequired,
    parameters: T.object.isRequired,
    picking: T.object.isRequired
  }).isRequired,
  userEvaluation: T.shape(UserEvaluationType.propTypes)
}

const Overview = connect(
  (state) => ({
    empty: select.empty(state),
    editable: hasPermission('edit', resourceSelect.resourceNode(state)),
    quiz: select.quiz(state),
    userEvaluation: resourceSelect.resourceEvaluation(state)
  })
)(OverviewComponent)

export {
  Overview
}
