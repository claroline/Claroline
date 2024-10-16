import React from 'react'
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
import {PathSummary} from '#/plugin/path/resources/path/containers/summary'

const PlayerEnd = (props) =>
  <ResourceEnd
    contentText={get(props.path, 'end.message') ||
      <>
        <h2 className="h3">{trans('attempt_end_title', {}, 'path')}</h2>
        <p>{trans('attempt_end_info', {}, 'path')}</p>
      </>
    }
    attempt={props.attempt}
    display={{
      score: get(props.path, 'display.showScore'),
      scoreMax: get(props.path, 'score.total'),
      successScore: get(props.path, 'score.success'),
      feedback: !!get(props.path, 'evaluation.successMessage') || !!get(props.path, 'evaluation.failureMessage'),
      toolbar: get(props.path, 'end.navigation'),
      certificates: get(props.path, 'end.workspaceCertificates')
    }}
    feedbacks={{
      success: get(props.path, 'evaluation.successMessage'),
      failure: get(props.path, 'evaluation.failureMessage')
    }}
    actions={[
      {
        name: 'restart',
        type: LINK_BUTTON,
        label: trans('restart', {}, 'actions'),
        target: `${props.basePath}/play`,
        exact: true,
        primary: true,
        size: 'lg'
      }
    ].concat(get(props.path, 'end.back.type') ? [
      {
        name: 'home',
        type: URL_BUTTON, // we require a URL_BUTTON here to escape the embedded resource router
        label: get(props.path, 'end.back.label') || trans('return-home', {}, 'actions'),
        target: '#'+classes({
          [desktopRoute()]: 'desktop' === get(props.path, 'end.back.type'),
          [props.workspace ? workspaceRoute(props.workspace) : undefined]: 'workspace' === get(props.path, 'end.back.type'),
          [get(props.path, 'end.back.target') ? resourceRoute(get(props.path, 'end.back.target')) : undefined]: 'resource' === get(props.path, 'end.back.type')
        })
      }
    ] : [])}
  >
    <section className="resource-parameters mb-3">
      <h3 className="h2">{trans('summary')}</h3>
      <PathSummary className="component-container" />
    </section>
  </ResourceEnd>

PlayerEnd.propTypes = {
  basePath: T.string.isRequired,
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
  stepsProgression: T.object
}

export {
  PlayerEnd
}
