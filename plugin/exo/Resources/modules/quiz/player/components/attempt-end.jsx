import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {LINK_BUTTON} from '#/main/app/buttons'
import {route} from '#/main/core/workspace/routing'

import {HtmlText} from '#/main/core/layout/components/html-text'
import {ScoreGauge} from '#/main/core/layout/gauge/components/score'
import {selectors as resourceSelect} from '#/main/core/resource/store'

import {select as playerSelect} from '#/plugin/exo/quiz/player/selectors'
import {showCorrection, showScore} from '#/plugin/exo/resources/quiz/papers/restrictions'

// TODO : merge with PlayerRestrictions
// TODO : show number of attempts info

const AttemptEndComponent = props =>
  <div className="quiz-player">
    <div className="row">
      {props.showAttemptScore &&
        <div className="col-md-3 text-center">
          <ScoreGauge
            type="user"
            value={get(props.paper, 'score')}
            total={get(props.paper, 'total')}
            width={140}
            height={140}
            displayValue={value => undefined === value || null === value ? '?' : value+''}
          />
        </div>
      }

      <div className={props.showAttemptScore ? 'col-md-9':'col-md-12'}>
        {props.showAttemptScore &&
        get(props.paper, 'total') &&
        get(props.paper, 'structure.parameters.successScore') &&
        get(props.paper, 'structure.parameters.successMessage') &&
        (get(props.paper, 'score') / get(props.paper, 'total')) * 100 >= get(props.paper, 'structure.parameters.successScore') &&
          <div className="alert alert-info">
            <HtmlText>{get(props.paper, 'structure.parameters.successMessage')}</HtmlText>
          </div>
        }

        {props.showAttemptScore &&
        get(props.paper, 'total') &&
        get(props.paper, 'structure.parameters.successScore') &&
        get(props.paper, 'structure.parameters.failureMessage') &&
        (get(props.paper, 'score') / get(props.paper, 'total')) * 100 < get(props.paper, 'structure.parameters.successScore') &&
          <div className="alert alert-danger">
            <HtmlText>{get(props.paper, 'structure.parameters.failureMessage')}</HtmlText>
          </div>
        }

        {props.endMessage ?
          <HtmlText>{props.endMessage}</HtmlText> :
          <div>
            <h2 className="h4">{trans('attempt_end_title', {}, 'quiz')}</h2>
            <p>{trans('attempt_end_info', {}, 'quiz')}</p>
          </div>
        }

        {props.endNavigation &&
          <Toolbar
            buttonName="btn btn-block btn-emphasis"
            toolbar="test restart correction statistics home"
            actions={[
              {
                name: 'test',
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-flask',
                label: trans('test', {}, 'actions'),
                target: `${props.path}/test`,
                exact: true,
                primary: true,
                displayed: props.testMode
              }, {
                name: 'restart',
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-redo',
                label: trans('exercise_restart', {}, 'quiz'),
                target: `${props.path}/play`,
                exact: true,
                primary: true,
                displayed: props.hasMoreAttempts
              }, {
                name: 'correction',
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-check-double',
                label: trans('view_paper', {}, 'quiz'),
                target: `${props.path}/papers/${props.paper.id}`,
                displayed: props.showAttemptCorrection,
                primary: true
              }, {
                name: 'statistics',
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-bar-chart',
                label: trans('statistics', {}, 'quiz'),
                target: `${props.path}/statistics`,
                displayed: props.showStatistics
              }, {
                name: 'home',
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-home',
                label: trans('return-home', {}, 'actions'),
                target: route(props.workspace),
                displayed: !!props.workspace,
                exact: true
              }
            ]}
          />
        }
      </div>
    </div>
  </div>

AttemptEndComponent.propTypes = {
  path: T.string.isRequired,
  workspace: T.object,
  paper: T.shape({ // TODO : paper prop types
    id: T.string.isRequired,
    structure: T.object.isRequired,
    finished: T.bool.isRequired
  }).isRequired,
  testMode: T.bool.isRequired,
  hasMoreAttempts: T.bool.isRequired,
  endMessage: T.string,
  endNavigation: T.bool.isRequired,
  showStatistics: T.bool.isRequired,
  showAttemptScore: T.bool.isRequired,
  showAttemptCorrection: T.bool.isRequired
}

const AttemptEnd = connect(
  (state) => {
    const isAdmin = hasPermission('edit', resourceSelect.resourceNode(state)) || hasPermission('manage_papers', resourceSelect.resourceNode(state))
    const paper = playerSelect.paper(state)

    return {
      path: resourceSelect.path(state),
      workspace: resourceSelect.workspace(state),
      paper: paper,
      testMode: playerSelect.testMode(state),
      hasMoreAttempts: playerSelect.hasMoreAttempts(state),
      endMessage: playerSelect.quizEndMessage(state),
      endNavigation: playerSelect.quizEndNavigation(state),

      showAttemptScore: showScore(paper, isAdmin),
      showAttemptCorrection: showCorrection(paper, isAdmin),
      showStatistics: playerSelect.showStatistics(state)
    }
  }
)(AttemptEndComponent)

export {
  AttemptEnd
}
