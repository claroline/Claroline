import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {constants as baseConstants} from '#/main/core/constants'
import {constants} from '#/main/core/resource/constants'
import {ContentHtml} from '#/main/app/content/components/html'
import {Button} from '#/main/app/action/components/button'
import {Alert} from '#/main/app/alert/components/alert'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {ScoreGauge} from '#/main/core/layout/gauge/components/score'

const UserProgression = props =>
  <section className="user-progression">
    <h3 className="h2">{trans('my_progression')}</h3>

    <div className="panel panel-default">
      <div className="panel-body text-center">
        {props.score && props.score.displayed &&
          <ScoreGauge
            type="user"
            value={props.score.current}
            total={props.score.total}
            width={140}
            height={140}
            displayValue={value => undefined === value || null === value ? '?' : value+''}
          />
        }

        <h4 className="user-progression-status h5">
          {props.statusTexts[props.status] ?
            props.statusTexts[props.status] :
            constants.EVALUATION_STATUSES[props.status]
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
  status: T.string,
  statusTexts: T.object,
  score: T.shape({
    displayed: T.bool,
    current: T.number,
    total: T.number
  }),
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
  const displayed = props.displayed // Feedback are enabled
    && [
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
        alertTitle = trans('evaluation_passed_feedback')
        alertMessage = props.success
        break
      case baseConstants.EVALUATION_STATUS_FAILED:
        alertType = 'danger'
        alertTitle = trans('evaluation_failed_feedback')
        alertMessage = props.failure
        break
      case baseConstants.EVALUATION_STATUS_COMPLETED:
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
      >
        {alertMessage}
      </AlertBlock>
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
  status: baseConstants.EVALUATION_STATUS_NOT_ATTEMPTED,
  success: trans('evaluation_passed_feedback_msg'),
  failure: trans('evaluation_failed_feedback_msg')
}

const ResourceOverview = props =>
  <section className="resource-section resource-overview">
    <h2 className="sr-only">{trans('resource_overview', {}, 'resource')}</h2>

    <div className="row">
      <div className="user-column col-md-4">
        {props.progression &&
          <UserProgression
            {...props.progression}
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
        {props.progression.feedback &&
          <UserFeedback
            status={props.progression.status}
            {...props.progression.feedback}
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
  progression: T.shape({
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
      total: T.number
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
