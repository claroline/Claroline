import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {trans, number} from '#/main/app/intl'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {constants as baseConstants} from '#/main/core/constants'
import {constants} from '#/main/core/resource/constants'
import {ContentHtml} from '#/main/app/content/components/html'
import {Button} from '#/main/app/action/components/button'
import {Alert} from '#/main/app/alert/components/alert'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {ScoreGauge} from '#/main/core/layout/gauge/components/score'
import {UserEvaluation as UserEvaluationTypes} from '#/main/core/resource/prop-types'
import {LiquidGauge} from '#/main/core/layout/gauge/components/liquid-gauge'

const UserProgression = props =>
  <section className="user-progression">
    <h3 className="h2">{trans('my_progression')}</h3>

    <div className="panel panel-default">
      <div className="panel-body text-center">
        {props.showScore &&
          <ScoreGauge
            type="user"
            value={props.evaluation.score && props.scoreMax ? (props.evaluation.score / props.evaluation.scoreMax) * props.scoreMax : props.evaluation.score}
            total={props.scoreMax || props.evaluation.scoreMax}
            width={140}
            height={140}
            displayValue={value => undefined === value || null === value ? '?' : number(value)+''}
          />
        }

        {!props.showScore &&
          <LiquidGauge
            id="user-progression-overview"
            type="user"
            value={props.evaluation.progression && props.evaluation.progressionMax ? (props.evaluation.progression / props.evaluation.progressionMax) * 100 : props.evaluation.progression}
            displayValue={(value) => number(value) + '%'}
            width={140}
            height={140}
          />
        }

        <h4 className="user-progression-status h5">
          {props.statusTexts[props.evaluation.status] ?
            props.statusTexts[props.evaluation.status] :
            constants.EVALUATION_STATUSES[props.evaluation.status]
          }
        </h4>
      </div>

      {0 !== props.details.length &&
        <ul className="list-group list-group-values">
          {props.details.map((info, index) =>
            <li key={index} className="list-group-item">
              {info[0]}
              <span className="value">{info[1]}</span>
            </li>
          )}
        </ul>
      }
    </div>
  </section>

UserProgression.propTypes = {
  statusTexts: T.object,
  showScore: T.bool,
  scoreMax: T.number,
  score: T.shape({
    displayed: T.bool,
    current: T.number,
    total: T.number
  }),
  evaluation: T.shape(
    UserEvaluationTypes.propTypes
  ).isRequired,
  details: T.arrayOf(
    T.arrayOf(T.string)
  )
}

UserProgression.defaultProps = {
  status: baseConstants.EVALUATION_STATUS_NOT_ATTEMPTED,
  statusTexts: {},
  details: []
}

const UserFeedback = props => {
  const displayed = [
    baseConstants.EVALUATION_STATUS_PASSED,
    baseConstants.EVALUATION_STATUS_FAILED,
    baseConstants.EVALUATION_STATUS_COMPLETED
  ].indexOf(props.status) > -1 // Evaluation is finished

  if (displayed) {
    let alertType
    let alertTitle
    let alertMessage
    switch (props.status) {
      case baseConstants.EVALUATION_STATUS_PASSED:
        alertType = 'success'
        alertTitle = trans('evaluation_passed_feedback', {}, 'evaluation')
        alertMessage = props.success
        break
      case baseConstants.EVALUATION_STATUS_FAILED:
        alertType = 'danger'
        alertTitle = trans('evaluation_failed_feedback', {}, 'evaluation')
        alertMessage = props.failure
        break
      case baseConstants.EVALUATION_STATUS_COMPLETED:
      default:
        alertType = 'info'
        alertTitle = trans('evaluation_completed_feedback', {}, 'evaluation')
        alertMessage = trans('evaluation_completed_feedback_msg', {}, 'evaluation')
        break
    }

    return (
      <AlertBlock
        style={{
          marginTop: 20
        }}
        type={alertType}
        title={alertTitle}
      >
        <ContentHtml>{alertMessage}</ContentHtml>
      </AlertBlock>
    )
  }

  return null // feedback not available
}

UserFeedback.propTypes = {
  status: T.string,
  success: T.string,
  failure: T.string
}

UserFeedback.defaultProps = {
  status: baseConstants.EVALUATION_STATUS_NOT_ATTEMPTED,
  success: trans('evaluation_passed_feedback_msg', {}, 'evaluation'),
  failure: trans('evaluation_failed_feedback_msg', {}, 'evaluation')
}

const ResourceOverview = props =>
  <section className="resource-section resource-overview">
    <h2 className="sr-only">{trans('resource_overview', {}, 'resource')}</h2>

    <div className="row">
      <div className="user-column col-md-4">
        {!isEmpty(props.evaluation) &&
          <UserProgression
            evaluation={props.evaluation}
            statusTexts={props.statusTexts}
            details={props.details}
            showScore={get(props, 'display.score', false)}
            scoreMax={get(props, 'display.scoreMax')}
          />
        }

        {0 !== props.actions.length &&
          <section className="overview-user-actions">
            <h3 className="sr-only">{trans('resource_overview_actions', {}, 'resource')}</h3>

            {props.actions.map((action, index) => !action.disabled ?
              <Button
                {...omit(action, 'disabledMessages')}
                key={index}
                className={classes('btn btn-block', {
                  'btn-default': !action.primary && !action.dangerous,
                  'btn-primary': action.primary,
                  'btn-danger': action.dangerous
                })}
              /> :
              action.disabledMessages && action.disabledMessages.map((message, messageIndex) =>
                <Alert key={messageIndex} type="warning">{message}</Alert>
              )
            )}
          </section>
        }
      </div>

      <div className="resource-column col-md-8">
        {!isEmpty(props.evaluation) && get(props, 'display.feedback', false) &&
          <UserFeedback
            status={props.evaluation.status}
            {...props.feedbacks}
          />
        }

        {props.contentText &&
          <section className="resource-info">
            <h3 className="h2">{trans('resource_overview_info', {}, 'resource')}</h3>

            <div className="panel panel-default">
              {typeof props.contentText === 'string' ?
                <ContentHtml className="panel-body">{props.contentText}</ContentHtml>
                :
                <div className="panel-body">{props.contentText}</div>
              }
            </div>
          </section>
        }

        {props.children}
      </div>
    </div>
  </section>

ResourceOverview.propTypes = {
  contentText: T.node, // can be a string or an empty placeholder
  evaluation: T.shape(
    UserEvaluationTypes.propTypes
  ),
  display: T.shape({
    score: T.bool,
    scoreMax: T.number,
    feedback: T.bool
  }),
  feedbacks: T.shape({
    success: T.string,
    failure: T.string
  }),
  statusTexts: T.object,
  details: T.arrayOf(
    T.arrayOf(T.string)
  ),
  actions: T.arrayOf(T.shape(
    merge({}, ActionTypes.propTypes, {
      disabledMessages: T.arrayOf(T.string)
    })
  )),
  children: T.node
}

ResourceOverview.defaultProps = {
  actions: []
}

export {
  ResourceOverview
}
