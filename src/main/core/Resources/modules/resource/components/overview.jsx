import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {displayDate, trans} from '#/main/app/intl'
import {toKey} from '#/main/core/scaffolding/text'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {ContentHtml} from '#/main/app/content/components/html'
import {Button} from '#/main/app/action/components/button'
import {Alert} from '#/main/app/alert/components/alert'
import {AlertBlock} from '#/main/app/alert/components/alert-block'

import {constants} from '#/main/evaluation/resource/constants'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'
import {EvaluationFeedback} from '#/main/evaluation/components/feedback'
import {EvaluationDetails} from '#/main/evaluation/components/details'

const ResourceOverview = props =>
  <section className="resource-overview">
    <h2 className="sr-only">{trans('resource_overview', {}, 'resource')}</h2>

    <div className="row">
      <div className="col-md-4">
        <section className="user-progression">
          <h3 className="h2">{trans('my_progression')}</h3>
          {!isEmpty(props.evaluation) &&
            <EvaluationDetails
              evaluation={props.evaluation}
              statusTexts={merge({}, constants.EVALUATION_STATUSES, props.statusTexts || {})}
              details={[
                [trans('last_activity'), get(props.evaluation, 'date') ? displayDate(props.evaluation.date, false, true) : '-']
              ].concat(props.details || [])}
              showScore={get(props, 'display.score', false)}
              scoreMax={get(props, 'display.scoreMax')}
              successScore={get(props, 'display.successScore')}
              estimatedDuration={get(props, 'resourceNode.evaluation.estimatedDuration')}
            />
          }
        </section>

        {0 !== props.actions.length &&
          <section className="overview-user-actions">
            <h3 className="sr-only">{trans('resource_overview_actions', {}, 'resource')}</h3>

            {props.actions
              .filter(action => undefined === action.displayed || action.displayed)
              .map((action, index) => !action.disabled ?
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
              )
            }
          </section>
        }
      </div>

      <div className="col-md-8">
        {((!isEmpty(props.evaluation) && get(props, 'display.feedback', false)) || !isEmpty(get(props.feedbacks, 'closed'))) &&
          <section className="resource-feedbacks">
            {!isEmpty(props.evaluation) && get(props, 'display.feedback', false) &&
              <EvaluationFeedback
                status={props.evaluation.status}
                {...props.feedbacks}
              />
            }

            {!isEmpty(get(props.feedbacks, 'closed')) && props.feedbacks.closed.map(closedMessage =>
              <AlertBlock key={toKey(closedMessage[0])} type="warning" title={closedMessage[0]}>
                <ContentHtml>{closedMessage[1]}</ContentHtml>
              </AlertBlock>
            )}
          </section>
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
  resourceNode: T.object,
  evaluation: T.shape(
    ResourceEvaluationTypes.propTypes
  ),
  display: T.shape({
    score: T.bool,
    scoreMax: T.number,
    successScore: T.number,
    feedback: T.bool
  }),
  feedbacks: T.shape({
    success: T.string,
    failure: T.string,
    // a list of message to explain why the user can not submit new attempts to the resource (if quiz max attempts are reached, dropzone drop period finished, etc.)
    closed: T.arrayOf(T.array)
  }),
  statusTexts: T.object,

  /**
   * A list of detailed information about the evaluation.
   * Each info to display is an array of 2 elements : the first element is the label and the second is the associated value.
   */
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
