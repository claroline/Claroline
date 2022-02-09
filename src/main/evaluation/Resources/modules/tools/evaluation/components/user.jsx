import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans, displayDate, displayDuration, number} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {Alert} from '#/main/app/alert/components/alert'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentTitle} from '#/main/app/content/components/title'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {UserMicro} from '#/main/core/user/components/micro'
import {displayUsername} from '#/main/core/user/utils'
import {LiquidGauge} from '#/main/core/layout/gauge/components/liquid-gauge'

import {constants as baseConstants} from '#/main/core/constants'
import {constants} from '#/main/core/workspace/constants'
import {UserEvaluation as WorkspaceUserEvaluationTypes} from '#/main/core/workspace/prop-types'
import {ResourceUserEvaluation as ResourceUserEvaluationTypes} from '#/main/evaluation/resource/prop-types'
import {ResourceCard} from '#/main/evaluation/resource/components/card'

const WorkspaceProgression = (props) => {
  let progression = 0
  if (props.workspaceEvaluation.progression) {
    progression = props.workspaceEvaluation.progression
    if (props.workspaceEvaluation.progressionMax) {
      progression = (progression / props.workspaceEvaluation.progressionMax) * 100
    }
  }

  return (
    <div className="panel panel-default">
      <div className="panel-heading">
        <UserMicro
          className="content-creator"
          {...get(props.workspaceEvaluation, 'user', {})}
          link={true}
        />
      </div>

      <div className="panel-body text-center">
        <LiquidGauge
          id={`user-progression-${props.workspaceEvaluation.id}`}
          type="user"
          value={progression}
          displayValue={(value) => number(value) + '%'}
          width={140}
          height={140}
        />

        <h4 className="user-progression-status h5">
          {constants.EVALUATION_STATUSES[get(props.workspaceEvaluation, 'status', baseConstants.EVALUATION_STATUS_UNKNOWN)]}
        </h4>
      </div>

      <ul className="list-group list-group-values">
        <li className="list-group-item">
          {trans('last_modification')}
          <span className="value">{get(props.workspaceEvaluation, 'date') ? displayDate(props.workspaceEvaluation.date, false, true) : '-'}</span>
        </li>

        <li className="list-group-item">
          {trans('duration')}
          <span className="value">{get(props.workspaceEvaluation, 'duration') ? displayDuration(get(props.workspaceEvaluation, 'duration')) : '-'}</span>
        </li>

        {get(props.workspaceEvaluation, 'scoreMax') &&
          <li className="list-group-item">
            {trans('score')}
            <span className="value">
              {get(props.workspaceEvaluation, 'score') ? number((get(props.workspaceEvaluation, 'score') / get(props.workspaceEvaluation, 'scoreMax')) * 100) : '?'} / 100
            </span>
          </li>
        }
      </ul>
    </div>
  )
}

WorkspaceProgression.propTypes = {
  workspaceEvaluation: T.shape(
    WorkspaceUserEvaluationTypes.propTypes
  ).isRequired
}

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
            actions={[
              {
                name: 'export',
                type: URL_BUTTON,
                icon: 'fa fa-fw fa-download',
                label: trans('export', {}, 'actions'),
                target: ['apiv2_workspace_export_user_progression', {workspace: this.props.workspaceId, user: this.props.userId}],
                group: trans('export')
              }
            ]}
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
            <WorkspaceProgression
              workspaceEvaluation={this.props.workspaceEvaluation}
            />
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
  userId: T.string.isRequired,
  workspaceId: T.string.isRequired,
  backAction: T.shape({
    // TODO : action types
  }),

  // from store
  loaded: T.bool.isRequired,
  workspaceEvaluation: T.shape(
    WorkspaceUserEvaluationTypes.propTypes
  ),
  resourceEvaluations: T.arrayOf(T.shape(
    ResourceUserEvaluationTypes.propTypes
  )),
  load: T.func.isRequired
}

export {
  EvaluationUser
}
