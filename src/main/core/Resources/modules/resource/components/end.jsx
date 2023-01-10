import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl'
import {ContentHtml} from '#/main/app/content/components/html'
import {Toolbar} from '#/main/app/action'
import {Action as ActionTypes} from '#/main/app/action/prop-types'

import {constants} from '#/main/evaluation/resource/constants'
import {ResourceAttempt as ResourceAttemptTypes} from '#/main/evaluation/resource/prop-types'
import {EvaluationFeedback} from '#/main/evaluation/components/feedback'
import {EvaluationDetails} from '#/main/evaluation/components/details'
import {AlertBlock} from '#/main/app/alert/components/alert-block'

const ResourceEnd = (props) =>
  <section className="resource-end">
    <h2 className="sr-only">{trans('resource_end', {}, 'resource')}</h2>

    <div className="row">
      <div className="col-md-4">
        {!isEmpty(props.attempt) &&
          <EvaluationDetails
            evaluation={props.attempt}
            statusTexts={merge({}, constants.EVALUATION_STATUSES, props.statusTexts || {})}
            details={props.details}
            showScore={get(props, 'display.score', false)}
            scoreMax={get(props, 'display.scoreMax')}
            successScore={get(props, 'display.successScore')}
          />
        }
      </div>

      <div className="col-md-8">
        {((!isEmpty(props.attempt) && get(props, 'display.feedback', false) || !isEmpty(props.feedbacks.closed))) &&
          <section className="resource-feedbacks">
            <EvaluationFeedback
              status={props.attempt.status}
              {...props.feedbacks}
            />

            {!isEmpty(props.feedbacks.closed) && props.feedbacks.closed.map(closedMessage =>
              <AlertBlock type="warning" title={closedMessage[0]}>
                <ContentHtml>{closedMessage[1]}</ContentHtml>
              </AlertBlock>
            )}
          </section>
        }

        {props.contentText &&
          <section className="resource-info">
            <div className="panel panel-default">
              {typeof props.contentText === 'string' ?
                <ContentHtml className="panel-body">{props.contentText}</ContentHtml>
                :
                <div className="panel-body">{props.contentText}</div>
              }
            </div>
          </section>
        }

        {get(props, 'display.toolbar') &&
          <Toolbar
            className="component-container"
            buttonName="btn btn-block"
            actions={props.actions}
          />
        }

        {props.children}
      </div>
    </div>
  </section>

ResourceEnd.propTypes = {
  contentText: T.node, // can be a string or an empty placeholder
  attempt: T.shape(
    ResourceAttemptTypes.propTypes
  ),
  display: T.shape({
    score: T.bool,
    scoreMax: T.number,
    successScore: T.number,
    feedback: T.bool,
    toolbar: T.bool
  }),
  statusTexts: T.object,
  /**
   * A list of detailed information about the evaluation.
   * Each info to display is an array of 2 elements : the first element is the label and the second is the associated value.
   */
  details: T.arrayOf(
    T.arrayOf(T.string)
  ),
  feedbacks: T.shape({
    success: T.string,
    failure: T.string,
    // a list of message to explain why the user can not submit new attempts to the resource (if quiz max attempts are reached, dropzone drop period finished, etc.)
    closed: T.arrayOf(T.array)
  }),
  actions: T.arrayOf(T.shape(
    merge({}, ActionTypes.propTypes, {
      disabledMessages: T.arrayOf(T.string)
    })
  )),
  children: T.node
}

export {
  ResourceEnd
}
