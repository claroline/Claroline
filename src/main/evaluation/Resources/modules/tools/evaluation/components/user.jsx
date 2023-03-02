import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans, displayDate, displayDuration, number} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {Alert} from '#/main/app/alert/components/alert'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentTitle} from '#/main/app/content/components/title'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {displayUsername} from '#/main/community/utils'
import {MODAL_MESSAGE} from '#/plugin/message/modals/message'
import {ContentHelp} from '#/main/app/content/components/help'

import {constants as baseConstants} from '#/main/evaluation/constants'
import {constants} from '#/main/evaluation/workspace/constants'
import {WorkspaceEvaluation as WorkspaceEvaluationTypes} from '#/main/evaluation/workspace/prop-types'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'
import {ResourceCard} from '#/main/evaluation/resource/components/card'
import {route as resourceRoute} from '#/main/core/resource/routing'
import {MODAL_RESOURCE_EVALUATIONS} from '#/main/evaluation/modals/resource-evaluations'
import {EvaluationDetails} from '#/main/evaluation/components/details'
import {route} from '#/main/community/user/routing'

class EvaluationUser extends Component {
  constructor(props) {
    super(props)

    // TODO : filter in query
    this.state = {
      resources: 'required'
    }
  }

  componentDidMount() {
    if (!this.props.loaded || this.props.userId !== get(this.props.workspaceEvaluation, 'user.id') || this.props.workspaceId !== get(this.props.workspaceEvaluation, 'workspace.id')) {
      this.load()
    }
  }

  componentDidUpdate(prevProps) {
    if (prevProps.loaded !== this.props.loaded || prevProps.workspaceId !== this.props.workspaceId || prevProps.userId !== this.props.userId) {
      this.load()
    }
  }

  load() {
    if (!this.props.loaded || this.props.userId !== get(this.props.workspaceEvaluation, 'user.id') || this.props.workspaceId !== get(this.props.workspaceEvaluation, 'workspace.id')) {
      this.props.load(this.props.workspaceId, this.props.userId)
    }
  }

  render() {
    if (!this.props.loaded) {
      return (
        <ContentLoader
          className="row"
          size="lg"
          description="Nous chargeons la progression..."
        />
      )
    }

    return (
      <Fragment>
        {this.props.backAction &&
          <ContentTitle
            title={displayUsername(get(this.props.workspaceEvaluation, 'user'))}
            backAction={this.props.backAction}
          />
        }

        <div
          className="row"
          style={{
            marginTop: this.props.backAction ? 0 : '20px', // FIXME
            marginBottom: '10px'
          }}
        >
          <div className="col-md-4 user-progression">
            <EvaluationDetails
              evaluation={this.props.workspaceEvaluation}
              statusTexts={constants.EVALUATION_STATUSES}
              details={[
                [trans('last_activity'), get(this.props.workspaceEvaluation, 'date') ? displayDate(this.props.workspaceEvaluation.date, false, true) : '-'],
                [trans('duration'), get(this.props.workspaceEvaluation, 'duration') ? displayDuration(get(this.props.workspaceEvaluation, 'duration')) : '-'],
                get(this.props.workspaceEvaluation, 'scoreMax') && [
                  trans('score'),
                  (get(this.props.workspaceEvaluation, 'score') ? number((get(this.props.workspaceEvaluation, 'score') / get(this.props.workspaceEvaluation, 'scoreMax')) * 100) : '?') + ' / 100'
                ]
              ].filter(value => !!value)}
              estimatedDuration={get(this.props, 'workspace.evaluation.estimatedDuration')}
            />

            <div className="component-container">
              <Toolbar
                buttonName="btn btn-block"
                toolbar="show-profile send-message"
                actions={[
                  {
                    name: 'show-profile',
                    className: 'btn-emphasis',
                    type: LINK_BUTTON,
                    label: trans('show_profile', {}, 'actions'),
                    target: route(get(this.props.workspaceEvaluation, 'user')),
                    primary: true,
                    displayed: this.props.userId !== this.props.currentUserId
                  }, {
                    name: 'send-message',
                    type: MODAL_BUTTON,
                    label: trans('send-message', {}, 'actions'),
                    modal: [MODAL_MESSAGE, {
                      receivers: {users: [get(this.props.workspaceEvaluation, 'user')]}
                    }],
                    displayed: this.props.userId !== this.props.currentUserId
                  }, {
                    name: 'download-participation-certificate',
                    className: 'btn-emphasis',
                    type: URL_BUTTON,
                    label: trans('download_participation_certificate', {}, 'actions'),
                    target: ['apiv2_workspace_download_participation_certificate', {
                      workspace: get(this.props.workspaceEvaluation, 'workspace.id'),
                      user: get(this.props.workspaceEvaluation, 'user.id')
                    }],
                    displayed: [
                      baseConstants.EVALUATION_STATUS_COMPLETED,
                      baseConstants.EVALUATION_STATUS_PARTICIPATED
                    ].includes(get(this.props.workspaceEvaluation, 'status', baseConstants.EVALUATION_STATUS_UNKNOWN))
                  }, {
                    name: 'download-success-certificate',
                    className: 'btn-emphasis',
                    type: URL_BUTTON,
                    label: trans('download_success_certificate', {}, 'actions'),
                    target: ['apiv2_workspace_download_success_certificate', {
                      workspace: get(this.props.workspaceEvaluation, 'workspace.id'),
                      user: get(this.props.workspaceEvaluation, 'user.id')
                    }],
                    displayed: baseConstants.EVALUATION_STATUS_PASSED === get(this.props.workspaceEvaluation, 'status', baseConstants.EVALUATION_STATUS_UNKNOWN)
                  }
                ]}
              />

              <ContentHelp
                help={trans('workspace_certificates_availability_help', {}, 'workspace')}
              />
            </div>
          </div>

          <div className="col-md-8">
            {isEmpty(this.props.resourceEvaluations) &&
              <ContentPlaceholder
                size="lg"
                icon="fa fa-folder"
                title={trans('no_started_resource', {}, 'resource')}
                help={trans('no_started_resource_help', {}, 'resource')}
              />
            }

            {!isEmpty(this.props.resourceEvaluations) &&
              <ul className="nav nav-tabs component-container">
                <li>
                  <Button
                    type={CALLBACK_BUTTON}
                    label={trans('required_resources', {}, 'resource')}
                    callback={() => this.setState({resources: 'required'})}
                    active={'required' === this.state.resources}
                  />
                </li>
                <li>
                  <Button
                    type={CALLBACK_BUTTON}
                    label={trans('all_resources', {}, 'resource')}
                    callback={() => this.setState({resources: 'all'})}
                    active={'all' === this.state.resources}
                  />
                </li>
              </ul>
            }

            <Alert type="info">
              {'all' === this.state.resources ?
                trans('all_resources_help', {}, 'resource') :
                trans('required_resources_help', {}, 'resource')
              }
            </Alert>

            {this.props.resourceEvaluations
              .filter(evaluation => 'all' === this.state.resources || evaluation.required)
              .map(evaluation => (
                <ResourceCard
                  key={evaluation.id}
                  style={{
                    marginBottom: '10px'
                  }}
                  data={evaluation}
                  actions={[
                    {
                      name: 'open',
                      type: LINK_BUTTON,
                      icon: 'fa fa-fw fa-external-link',
                      label: trans('open', {}, 'actions'),
                      target: resourceRoute(evaluation.resourceNode)
                    }, {
                      name: 'about',
                      type: MODAL_BUTTON,
                      icon: 'fa fa-fw fa-circle-info',
                      label: trans('show-info', {}, 'actions'),
                      modal: [MODAL_RESOURCE_EVALUATIONS, {
                        userEvaluation: evaluation
                      }]
                    }
                  ]}
                />
              ))
            }
          </div>
        </div>
      </Fragment>
    )
  }
}

EvaluationUser.propTypes = {
  contextPath: T.string,
  currentUserId: T.string,
  userId: T.string.isRequired,
  workspaceId: T.string.isRequired,
  backAction: T.shape({
    // TODO : action types
  }),

  // from store
  loaded: T.bool.isRequired,
  workspaceEvaluation: T.shape(
    WorkspaceEvaluationTypes.propTypes
  ),
  resourceEvaluations: T.arrayOf(T.shape(
    ResourceEvaluationTypes.propTypes
  )),
  load: T.func.isRequired
}

export {
  EvaluationUser
}
