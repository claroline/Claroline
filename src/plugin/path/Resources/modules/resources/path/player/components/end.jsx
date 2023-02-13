import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {scrollTo} from '#/main/app/dom/scroll'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {route as desktopRoute} from '#/main/core/tool/routing'
import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {route as resourceRoute} from '#/main/core/resource/routing'
import {ResourceAttempt as ResourceAttemptTypes, ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'

import {ResourceEnd} from '#/main/core/resource/components/end'
import {Path as PathTypes} from '#/plugin/path/resources/path/prop-types'
import {PathSummary} from '#/plugin/path/resources/path/components/summary'

class PlayerEnd extends Component {
  constructor(props) {
    super(props)

    this.state = {
      loaded: !this.props.currentUser
    }
  }

  componentDidMount() {
    if (!this.state.loaded) {
      this.props.getAttempt(this.props.path.id).then(() => this.setState({loaded: true}))
    }
  }

  render() {
    return (
      <ResourceEnd
        contentText={get(this.props.path, 'end.message') ||
          <Fragment>
            <h2 className="h3">{trans('attempt_end_title', {}, 'path')}</h2>
            <p>{trans('attempt_end_info', {}, 'path')}</p>
          </Fragment>
        }
        attempt={this.props.attempt}
        workspace={this.props.workspace}
        display={{
          score: get(this.props.path, 'display.showScore'),
          scoreMax: get(this.props.path, 'score.total'),
          successScore: get(this.props.path, 'score.success'),
          feedback: !!get(this.props.path, 'evaluation.successMessage') || !!get(this.props.path, 'evaluation.failureMessage'),
          toolbar: get(this.props.path, 'end.navigation'),
          certificates: get(this.props.path, 'end.workspaceCertificates')
        }}
        feedbacks={{
          success: get(this.props.path, 'evaluation.successMessage'),
          failure: get(this.props.path, 'evaluation.failureMessage')
        }}
        actions={[
          {
            name: 'restart',
            type: LINK_BUTTON,
            label: trans('restart_path', {}, 'path'),
            target: `${this.props.basePath}/play`,
            exact: true,
            primary: true,
            className: 'btn-emphasis'
          }
        ].concat(get(this.props.path, 'end.back.type') ? [
          {
            name: 'home',
            type: URL_BUTTON, // we require an URL_BUTTON here to escape the embedded resource router
            label: get(this.props.path, 'end.back.label') || trans('return-home', {}, 'actions'),
            target: '#'+classes({
              [desktopRoute()]: 'desktop' === get(this.props.path, 'end.back.type'),
              [this.props.workspace ? workspaceRoute(this.props.workspace) : undefined]: 'workspace' === get(this.props.path, 'end.back.type'),
              [get(this.props.path, 'end.back.target') ? resourceRoute(get(this.props.path, 'end.back.target')) : undefined]: 'resource' === get(this.props.path, 'end.back.type')
            })
          }
        ] : [])}
      >
        <section className="resource-parameters">
          <h3 className="h2">{trans('summary')}</h3>
          <PathSummary
            className="component-container"
            basePath={this.props.basePath}
            path={this.props.path}
            stepsProgression={this.props.stepsProgression}
            resourceEvaluations={this.props.resourceEvaluations}
            onNavigate={() => {
              scrollTo(`#resource-${this.props.resourceId} > .page-content`)
            }}
          />
        </section>
      </ResourceEnd>
    )
  }
}

PlayerEnd.propTypes = {
  basePath: T.string.isRequired,
  resourceId: T.string.isRequired,
  path: T.shape(
    PathTypes.propTypes
  ).isRequired,
  workspace: T.object,
  currentUser: T.object,
  attempt: T.shape(
    ResourceAttemptTypes.propTypes
  ),
  resourceEvaluations: T.arrayOf(T.shape(
    ResourceEvaluationTypes.propTypes
  )),
  stepsProgression: T.object,
  getAttempt: T.func.isRequired
}

export {
  PlayerEnd
}
