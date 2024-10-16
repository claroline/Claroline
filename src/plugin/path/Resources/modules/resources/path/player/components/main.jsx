import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {ResourceAttempt as ResourceAttemptTypes, ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'

import {Path as PathTypes, Step as StepTypes} from '#/plugin/path/resources/path/prop-types'
import {Step} from '#/plugin/path/resources/path/player/components/step'
import {PlayerEnd} from '#/plugin/path/resources/path/player/components/end'
import {getNumbering} from '#/plugin/path/resources/path/utils'
import {ResourcePage} from '#/main/core/resource'
import {PathNav} from '#/plugin/path/resources/path/components/nav'
import {ProgressBar} from '#/main/app/content/components/progress-bar'

const PlayerMain = props => {
  if (0 === props.steps.length) {
    return (
      <ContentPlaceholder
        size="lg"
        title={trans('no_step', {}, 'path')}
      />
    )
  }

  return (
    <Routes
      path={props.basePath}
      redirect={[
        {from: '/play', to: `/play/${props.steps[0].slug}`}
      ]}
      routes={[
        {
          path: '/play/end',
          disabled: !get(props.path, 'end.display'),
          render: () => (
            <PlayerEnd
              basePath={props.basePath}
              path={props.path}
              currentUser={props.currentUser}
              attempt={props.attempt}
              resourceEvaluations={props.resourceEvaluations}
              stepsProgression={props.stepsProgression}
            />
          )
        }, {
          path: '/play/:slug',
          onEnter: (params) => {
            const step = props.steps.find(step => params.slug === step.slug)

            if (step && props.currentUser) {
              props.updateProgression(step.id)
            }
          },
          // force navigation in case the user as navigated with the summary without finishing an opened resource
          onLeave: () => props.enableNavigation(),
          render: (routeProps) => {
            const stepIndex = props.steps.findIndex(step => routeProps.match.params.slug === step.slug)
            if (-1 !== stepIndex) {
              const step = props.steps[stepIndex]

              const Current =
                <ResourcePage poster={step.poster}>
                  <ProgressBar
                    className="progress-minimal"
                    value={Math.floor(((stepIndex+1) / (props.steps.length)) * 100)}
                    size="xs"
                    type="primary"
                  />

                  <Step
                    {...step}
                    currentUser={props.currentUser}
                    numbering={getNumbering(props.path.display.numbering, props.path.steps, step)}
                    progression={props.stepsProgression[step.id]}
                    manualProgressionAllowed={props.path.display.manualProgressionAllowed}
                    updateProgression={props.updateProgression}
                    enableNavigation={props.enableNavigation}
                    disableNavigation={props.disableNavigation}
                    secondaryResourcesTarget={props.path.opening.secondaryResources}
                  />

                  {props.navigationEnabled &&
                    <PathNav
                      className="content-md mb-4"
                      resourceId={props.resourceId}
                      path={`${props.basePath}/play`}
                      current={step}
                      all={props.steps}
                      endPage={get(props.path, 'end.display')}
                    />
                  }
                </ResourcePage>
              return Current
            }

            routeProps.history.push(props.basePath+'/play')

            return null
          }
        }
      ]}
    />
  )
}

PlayerMain.propTypes = {
  basePath: T.string.isRequired,
  currentUser: T.object,
  navigationEnabled: T.bool.isRequired,
  resourceId: T.string.isRequired,
  path: T.shape(
    PathTypes.propTypes
  ).isRequired,
  steps: T.arrayOf(T.shape(
    StepTypes.propTypes
  )),
  stepsProgression: T.object,
  attempt: T.shape(
    ResourceAttemptTypes.propTypes
  ),
  resourceEvaluations: T.arrayOf(T.shape(
    ResourceEvaluationTypes.propTypes
  )),
  updateProgression: T.func.isRequired,
  enableNavigation: T.func.isRequired,
  disableNavigation: T.func.isRequired
}

export {
  PlayerMain
}
