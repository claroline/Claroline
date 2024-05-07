import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans, displayDate, displayDuration, number} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {Alert} from '#/main/app/alert/components/alert'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {MODAL_MESSAGE} from '#/plugin/message/modals/message'

import {constants as baseConstants} from '#/main/evaluation/constants'
import {constants} from '#/main/evaluation/workspace/constants'
import {WorkspaceEvaluation as WorkspaceEvaluationTypes} from '#/main/evaluation/workspace/prop-types'
import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'
import {ResourceCard} from '#/main/evaluation/resource/components/card'
import {route as resourceRoute} from '#/main/core/resource/routing'
import {MODAL_RESOURCE_EVALUATIONS} from '#/main/evaluation/modals/resource-evaluations'
import {EvaluationDetails} from '#/main/evaluation/components/details'
import {route} from '#/main/community/user/routing'
import {EvaluationJumbotron} from '#/main/evaluation/components/jumbotron'
import {PageSection} from '#/main/app/page/components/section'

class WorkspaceEvaluation extends Component {
  constructor(props) {
    super(props)

    // TODO : filter in query
    this.state = {
      resources: 'required'
    }
  }

  render() {


    return (
      <>
        {/*<EvaluationJumbotron
          evaluation={this.props.workspaceEvaluation}
        />*/}
        <PageSection size="md">
          <ul className="nav nav-tabs mb-3">
            <li className="nav-item">
              <Button
                className="nav-link"
                type={CALLBACK_BUTTON}
                label={trans('required_resources', {}, 'resource')}
                callback={() => this.setState({resources: 'required'})}
                active={'required' === this.state.resources}
              />
            </li>
            <li className="nav-item">
              <Button
                className="nav-link"
                type={CALLBACK_BUTTON}
                label={trans('all_resources', {}, 'resource')}
                callback={() => this.setState({resources: 'all'})}
                active={'all' === this.state.resources}
              />
            </li>
          </ul>

          <Alert type="info">
            {'all' === this.state.resources ?
              trans('all_resources_help', {}, 'resource') :
              trans('required_resources_help', {}, 'resource')
            }
          </Alert>

          {isEmpty(this.props.resourceEvaluations) &&
            <ContentPlaceholder
              size="lg"
              icon="fa fa-folder"
              title={trans('no_started_resource', {}, 'resource')}
              help={trans('no_started_resource_help', {}, 'resource')}
            />
          }

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
        </PageSection>
      </>
    )
  }
}

WorkspaceEvaluation.propTypes = {
  workspaceEvaluation: T.shape(
    WorkspaceEvaluationTypes.propTypes
  ),
  resourceEvaluations: T.arrayOf(T.shape(
    ResourceEvaluationTypes.propTypes
  ))
}

export {
  WorkspaceEvaluation
}
