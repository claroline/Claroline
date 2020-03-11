import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import {schemeCategory20c} from 'd3-scale'

import {asset} from '#/main/app/config/asset'
import {toKey} from '#/main/core/scaffolding/text'
import {trans, displayDate, displayDuration, number} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Alert} from '#/main/app/alert/components/alert'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentTitle} from '#/main/app/content/components/title'
import {EmptyPlaceholder} from '#/main/app/content/components/placeholder'
import {UserMicro} from '#/main/core/user/components/micro'
import {displayUsername} from '#/main/core/user/utils'
import {DataCard} from '#/main/app/data/components/card'
import {LiquidGauge} from '#/main/core/layout/gauge/components/liquid-gauge'

import {route as resourceRoute} from '#/main/core/resource/routing'
import {constants as baseConstants} from '#/main/core/constants'
import {constants} from '#/main/core/workspace/constants'
import {UserEvaluation as WorkspaceUserEvaluationTypes} from '#/main/core/workspace/prop-types'
import {UserEvaluation as ResourceUserEvaluationTypes} from '#/main/core/resource/prop-types'
import {MODAL_RESOURCE_EVALUATIONS} from '#/plugin/analytics/tools/dashboard/progression/modals/resource-evaluations'

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
              {get(props.workspaceEvaluation, 'score') || '?'} / {get(props.workspaceEvaluation, 'scoreMax')}
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

class ProgressionUser extends Component {
  constructor(props) {
    super(props)

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
            <WorkspaceProgression
              workspaceEvaluation={this.props.workspaceEvaluation}
            />
          </div>

          <div className="col-md-8">
            {isEmpty(this.props.resourceEvaluations) &&
              <EmptyPlaceholder
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
              .map(evaluation => {
                let progression = 0
                if (evaluation.progression) {
                  progression = evaluation.progression
                  if (evaluation.progressionMax) {
                    progression = (progression / evaluation.progressionMax) * 100
                  }
                }

                return (
                  <DataCard
                    key={evaluation.id}
                    id={evaluation.id}
                    className="resource-evaluation-card"
                    style={{
                      marginBottom: '10px'
                    }}
                    poster={evaluation.resourceNode.thumbnail ? asset(evaluation.resourceNode.thumbnail.url) : null}
                    icon={
                      <LiquidGauge
                        id={`user-progression-${evaluation.id}`}
                        type="user"
                        value={progression}
                        displayValue={(value) => number(value) + '%'}
                        width={60}
                        height={60}
                      />
                    }
                    title={evaluation.resourceNode.name}
                    subtitle={trans(evaluation.resourceNode.meta.type, {}, 'resource')}
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
                        icon: 'fa fa-fw fa-info',
                        label: trans('show-info', {}, 'actions'),
                        modal: [MODAL_RESOURCE_EVALUATIONS, {
                          userEvaluation: evaluation
                        }]
                      }
                    ]}
                  >
                    <div className="resource-evaluation-details">
                      {[
                        {
                          icon: 'fa fa-fw fa-eye',
                          label: trans('views'),
                          value: number(evaluation.nbOpenings)
                        }, {
                          icon: 'fa fa-fw fa-redo',
                          label: trans('attempts'),
                          value: number(evaluation.nbAttempts)
                        }, {
                          icon: 'fa fa-fw fa-hourglass-half',
                          label: 'Temps passé',
                          value: displayDuration(evaluation.duration) || trans('unknown')
                        }, {
                          icon: 'fa fa-fw fa-award',
                          label: trans('score'),
                          displayed: !!evaluation.scoreMax,
                          value: (number(evaluation.score) || 0) + ' / ' + number(evaluation.scoreMax)
                        }
                      ]
                        .filter(item => undefined === item.displayed || item.displayed)
                        .map((item, index) => (
                          <article key={toKey(item.label)}>
                            <span className={item.icon} style={{backgroundColor: schemeCategory20c[(index * 4) + 1]}} />
                            <h5>
                              <small>{item.label}</small>
                              {item.value}
                            </h5>
                          </article>
                        ))
                      }
                    </div>
                  </DataCard>
                )
              })
            }
          </div>
        </div>
      </Fragment>
    )
  }
}

ProgressionUser.propTypes = {
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
  ProgressionUser
}
