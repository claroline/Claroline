import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans, number} from '#/main/app/intl'
import {ContentSummary} from '#/main/app/content/components/summary'
import {scrollTo} from '#/main/app/dom/scroll'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ContentHtml} from '#/main/app/content/components/html'
import {ScoreGauge} from '#/main/core/layout/gauge/components/score'
import {ScoreBox} from '#/main/core/layout/evaluation/components/score-box'
import {route} from '#/main/core/workspace/routing'

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
      this.props.getAttempt(this.props.pathId).then(() => this.setState({loaded: true}))
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
          {step.evaluated && this.props.showScore && get(this.props.attempt, `data.resources[${step.id}].max`, null) &&
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
      onClick: () => {
        scrollTo(`#resource-${this.props.resourceId} > .page-content`)
      }
    }
  }

  render() {
    return (
      <div className="row">
        {this.props.showScore && this.props.attempt &&
          <div className="col-md-3 text-center" style={{marginTop: 20}}>
            <ScoreGauge
              type="user"
              value={get(this.props.attempt, 'score') ? number(get(this.props.attempt, 'score')) : null}
              total={get(this.props.attempt, 'scoreMax')}
              width={140}
              height={140}
              displayValue={value => undefined === value || null === value ? '?' : value+''}
            />
          </div>
        }

        <div className={this.props.showScore && this.props.attempt ? 'col-md-9':'col-md-12'}>
          {this.props.endMessage ?
            <ContentHtml className="component-container" style={{marginTop: 20}}>{this.props.endMessage}</ContentHtml> :
            <Fragment>
              <h2 className="h3">{trans('attempt_end_title', {}, 'path')}</h2>
              <p>{trans('attempt_end_info', {}, 'path')}</p>
            </Fragment>
          }

          <Toolbar
            className="component-container"
            buttonName="btn btn-block btn-emphasis"
            toolbar="restart home"
            actions={[
              {
                name: 'restart',
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-redo',
                label: trans('restart_path', {}, 'path'),
                target: `${this.props.path}/play`,
                exact: true,
                primary: true
              }, {
                name: 'home',
                type: URL_BUTTON, // we require an URL_BUTTON here to escape the embedded resource router
                icon: 'fa fa-fw fa-home',
                label: trans('return-home', {}, 'actions'),
                target: '#'+route(this.props.workspace),
                displayed: !!this.props.workspace,
                exact: true
              }
            ]}
          />

          <ContentSummary
            className="component-container"
            links={this.props.steps.map(this.getStepSummary)}
            noCollapse={true}
          />
        </div>
      </div>
    )
  }
}

PlayerEnd.propTypes = {
  path: T.string.isRequired,
  resourceId: T.string.isRequired,
  pathId: T.string.isRequired,
  endMessage: T.string,
  showScore: T.bool,
  scoreTotal: T.number,
  workspace: T.object,
  steps: T.array,
  currentUser: T.object,
  attempt: T.object,
  getAttempt: T.func.isRequired
}

export {
  PlayerEnd
}
