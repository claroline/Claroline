import React, {useEffect, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'
import merge from 'lodash/merge'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl'
import {toKey} from '#/main/core/scaffolding/text'
import {ContentHtml} from '#/main/app/content/components/html'
import {Toolbar} from '#/main/app/action'
import {Action as ActionTypes} from '#/main/app/action/prop-types'

import {constants as evalConstants} from '#/main/evaluation/constants'
import {constants} from '#/main/evaluation/resource/constants'
import {ResourceAttempt as ResourceAttemptTypes} from '#/main/evaluation/resource/prop-types'
import {EvaluationFeedback} from '#/main/evaluation/components/feedback'
import {EvaluationDetails} from '#/main/evaluation/components/details'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {URL_BUTTON} from '#/main/app/buttons'

const WorkspaceCertificatesToolbar = (props) => {
  const [wsEval, setWsEval] = useState(null)

  useEffect(() => {
    fetch(url(['apiv2_workspace_evaluation_get', {workspace: props.workspace.id, user: props.currentUser.id}]), {
      credentials: 'include'
    })
      .then(response => response.json())
      .then(response => setWsEval(response))
  }, [props.workspace.id, props.currentUser.id])

  return (
    <Toolbar
      className="component-container"
      buttonName="btn btn-block"
      actions={[
        {
          name: 'download-participation-certificate',
          type: URL_BUTTON,
          label: trans('download_participation_certificate', {}, 'actions'),
          target: ['apiv2_workspace_download_participation_certificate', {
            workspace: get(wsEval, 'workspace.id'),
            user: get(wsEval, 'user.id')
          }],
          displayed: [
            evalConstants.EVALUATION_STATUS_COMPLETED,
            evalConstants.EVALUATION_STATUS_PARTICIPATED
          ].includes(get(wsEval, 'status', evalConstants.EVALUATION_STATUS_UNKNOWN))
        }, {
          name: 'download-success-certificate',
          type: URL_BUTTON,
          label: trans('download_success_certificate', {}, 'actions'),
          target: ['apiv2_workspace_download_success_certificate', {
            workspace: get(wsEval, 'workspace.id'),
            user: get(wsEval, 'user.id')
          }],
          displayed: evalConstants.EVALUATION_STATUS_PASSED === get(wsEval, 'status', evalConstants.EVALUATION_STATUS_UNKNOWN)
        }
      ]}
    />
  )
}

WorkspaceCertificatesToolbar.propTypes = {
  workspace: T.shape({
    id: T.string.isRequired
  }).isRequired,
  currentUser: T.shape({
    id: T.string.isRequired
  }).isRequired
}

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
            {!isEmpty(props.attempt) &&
              <EvaluationFeedback
                status={props.attempt.status}
                {...props.feedbacks}
              />
            }

            {!isEmpty(props.feedbacks.closed) && props.feedbacks.closed.map(closedMessage =>
              <AlertBlock key={toKey(closedMessage[0])} type="warning" title={closedMessage[0]}>
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

        {get(props, 'display.certificates') && props.workspace && get(props.attempt, 'user') &&
          <WorkspaceCertificatesToolbar
            workspace={props.workspace}
            currentUser={get(props.attempt, 'user')}
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
  workspace: T.object,
  display: T.shape({
    score: T.bool,
    scoreMax: T.number,
    successScore: T.number,
    feedback: T.bool,
    toolbar: T.bool,
    certificates: T.bool
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
