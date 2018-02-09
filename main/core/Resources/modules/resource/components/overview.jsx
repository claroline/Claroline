import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import merge from 'lodash/merge'

import {trans} from '#/main/core/translation'
import {t_res} from '#/main/core/resource/translation'
import {Action as ActionTypes} from '#/main/core/layout/button/prop-types'
import {constants as evaluationConstants} from '#/main/core/resource/evaluation/constants'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'
import {Action} from '#/main/core/layout/button/components/action.jsx'
import {Alert} from '#/main/core/layout/alert/components/alert.jsx'
import {AlertBlock} from '#/main/core/layout/alert/components/alert-block.jsx'
import {ScoreGauge} from '#/main/core/layout/evaluation/components/score-gauge.jsx'

const UserProgression = props =>
  <section className="user-progression">
    <h3 className="h2 h-first">{t_res('resource_overview_progression')}</h3>

    <div className="panel panel-default">
      <div className="panel-body">
        {props.score && props.score.displayed &&
          <ScoreGauge
            score={props.score.current}
            maxScore={props.score.total}
          />
        }

        <h4 className="user-progression-status h5">
          {props.statusTexts[props.status] ?
            props.statusTexts[props.status] :
            evaluationConstants.EVALUATION_STATUSES[props.status]
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
  unlocked: T.bool,
  status: T.string,
  statusTexts: T.object,
  score: T.shape({
    displayed: T.bool,
    current: T.number,
    total: T.number.isRequired
  }),
  details: T.arrayOf(
    T.arrayOf(T.string)
  )
}

UserProgression.defaultProps = {
  unlocked: false,
  status: evaluationConstants.EVALUATION_STATUS_NOT_ATTEMPTED,
  statusTexts: {},
  details: []
}

const UserFeedback = props => {
  const displayed = props.displayed // Feedback are enabled
    && [
      evaluationConstants.EVALUATION_STATUS_PASSED,
      evaluationConstants.EVALUATION_STATUS_FAILED,
      evaluationConstants.EVALUATION_STATUS_COMPLETED
    ].inArray(props.status) // Evaluation is finished

  if (displayed) {
    let alertType
    let alertTitle
    let alertMessage
    switch (props.status) {
      case evaluationConstants.EVALUATION_STATUS_PASSED:
        alertType = 'success'
        alertTitle = trans('evaluation_passed_feedback')
        alertMessage = props.success
        break
      case evaluationConstants.EVALUATION_STATUS_FAILED:
        alertType = 'danger'
        alertTitle = trans('evaluation_failed_feedback')
        alertMessage = props.failure
        break
      case evaluationConstants.EVALUATION_STATUS_COMPLETED:
      default:
        alertType = 'info'
        alertTitle = trans('evaluation_completed_feedback')
        alertMessage = trans('evaluation_completed_feedback_msg')
        break
    }

    return (
      <AlertBlock
        type={alertType}
        title={alertTitle}
        message={alertMessage}
      />
    )
  }

  return null // feedback not available
}

UserFeedback.propTypes = {
  status: T.string,
  displayed: T.bool.isRequired,
  success: T.string,
  failure: T.string
}

UserFeedback.defaultProps = {
  status: evaluationConstants.EVALUATION_STATUS_NOT_ATTEMPTED,
  success: trans('evaluation_passed_feedback_msg'),
  failure: trans('evaluation_failed_feedback_msg')
}

const ResourceOverview = props =>
  <section className="resource-section resource-overview">
    <h2 className="sr-only">{t_res('resource_overview')}</h2>

    <div className="row">
      <div className="user-column col-md-4">
        {props.progression &&
          <UserProgression
            {...props.progression}
          />
        }

        {0 !== props.actions.length &&
          <section className="user-actions">
            <h3 className="sr-only">{t_res('resource_overview_actions')}</h3>

            {props.actions.map((action, index) => !action.disabled ?
              <Action
                {...action}
                key={index}
                className={classes('btn-block', {
                  'btn-default': !action.primary && !action.dangerous,
                  'btn-primary': action.primary,
                  'btn-danger': action.dangerous
                })}
              /> :
              action.disabledMessages && action.disabledMessages.map((message, messageIndex) =>
                <Alert key={messageIndex} type="warning" message={message} />
              )
            )}
          </section>
        }
      </div>

      <div className="resource-column col-md-8">
        {props.progression.feedback &&
          <UserFeedback
            status={props.progression.status}
            {...props.progression.feedback}
          />
        }

        {props.contentText &&
          <section className="resource-info">
            <h3 className="h2 h-first">{t_res('resource_overview_info')}</h3>

            <div className="panel panel-default">
              {typeof props.contentText === 'string' ?
                <HtmlText className="panel-body">{props.contentText}</HtmlText>
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
  progression: T.shape({
    /**
     * Permits to show notation & feedback before the end of evaluation.
     * (Some resources like DropZone implement it)
     */
    unlocked: T.bool,
    status: T.string,
    statusTexts: T.object,
    feedback: T.shape({
      displayed: T.bool.isRequired,
      success: T.string,
      failure: T.string
    }),
    score: T.shape({
      displayed: T.bool.isRequired,
      current: T.number,
      total: T.number.isRequired
    }),
    details: T.arrayOf(
      T.arrayOf(T.string)
    )
  }),
  actions: T.arrayOf(T.shape(
    merge({}, ActionTypes.propTypes, {
      disabledMessages: T.arrayOf(T.string)
    })
  )),
  children: T.node
}

ResourceOverview.defaultProps = {
  progression: {},
  actions: []
}

export {
  ResourceOverview
}
