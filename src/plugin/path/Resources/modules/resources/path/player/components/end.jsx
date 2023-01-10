import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {ContentSummary} from '#/main/app/content/components/summary'
import {scrollTo} from '#/main/app/dom/scroll'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box'
import {route} from '#/main/core/workspace/routing'

import {ResourceEnd} from '#/main/core/resource/components/end'
import {Path as PathTypes} from '#/plugin/path/resources/path/prop-types'

class PlayerEnd extends Component {
  constructor(props) {
    super(props)

    this.state = {
      loaded: !this.props.currentUser
    }

    this.getStepSummary = this.getStepSummary.bind(this)
  }

  componentDidMount() {
    if (!this.state.loaded) {
      this.props.getAttempt(this.props.path.id).then(() => this.setState({loaded: true}))
    }
  }

  getStepSummary(step) {
    return {
      id: step.id,
      type: LINK_BUTTON,
      icon: classes('step-progression fa fa-fw fa-circle', get(step, 'userProgression.status')),
      label: (
        <Fragment>
          {step.title}
          {!isEmpty(step.primaryResource) && get(this.props.path, 'display.showScore') && get(this.props.attempt, `data.resources[${step.id}].max`, null) &&
            <ScoreBox
              score={get(this.props.attempt, `data.resources[${step.id}].score`, null)}
              scoreMax={get(this.props.attempt, `data.resources[${step.id}].max`)}
              size="sm"
              style={{marginLeft: 'auto'}}
            />
          }
        </Fragment>
      ),
      target: `${this.props.path}/play/${step.slug}`,
      children: step.children ? step.children.map(this.getStepSummary) : [],
      onClick: () => scrollTo(`#resource-${this.props.resourceId} > .page-content`)
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
        display={{
          score: get(this.props.path, 'display.showScore'),
          scoreMax: get(this.props.path, 'score.total'),
          successScore: get(this.props.path, 'score.success'),
          feedback: !!get(this.props.path, 'evaluation.successMessage') || !!get(this.props.path, 'evaluation.failureMessage'),
          toolbar: get(this.props.path, 'end.navigation')
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
          }, {
            name: 'home',
            type: URL_BUTTON, // we require an URL_BUTTON here to escape the embedded resource router
            label: trans('return-home', {}, 'actions'),
            target: '#'+route(this.props.workspace),
            displayed: !!this.props.workspace,
            exact: true
          }
        ]}
      >
        <section className="resource-parameters">
          <h3 className="h2">{trans('summary')}</h3>
          <ContentSummary
            className="component-container"
            links={this.props.path.steps.map(this.getStepSummary)}
            noCollapse={true}
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
  attempt: T.object,
  getAttempt: T.func.isRequired
}

export {
  PlayerEnd
}
