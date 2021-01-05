import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {constants} from '#/plugin/path/resources/path/constants'
import {Path as PathTypes, Step as StepTypes} from '#/plugin/path/resources/path/prop-types'
import {PathCurrent} from '#/plugin/path/resources/path/components/current'
import {Step} from '#/plugin/path/resources/path/player/components/step'
import {PlayerEnd} from '#/plugin/path/resources/path/player/components/end'
import {getNumbering, getStepUserProgression} from '#/plugin/path/resources/path/utils'

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
    <Fragment>
      <h2 className="sr-only">{trans('play')}</h2>
      <Routes
        path={props.basePath}
        redirect={[
          {from: '/play', to: `/play/${props.steps[0].slug}`}
        ]}
        routes={[
          {
            path: '/play/end',
            disabled: !props.path.display.showEndPage,
            render: () => (
              <PlayerEnd
                path={props.basePath}
                pathId={props.path.id}
                resourceId={props.resourceId}
                currentUser={props.currentUser}
                workspace={props.workspace}
                steps={props.path.steps}
                scoreTotal={props.path.score.total}
                showScore={props.path.display.showScore}
                endMessage={props.path.meta.endMessage}
                attempt={props.attempt}
                getAttempt={props.getAttempt}
              />
            )
          }, {
            path: '/play/:slug',
            onEnter: (params) => {
              const step = props.steps.find(step => params.slug === step.slug)

              if (props.currentUser && getStepUserProgression(props.steps, step.id) === constants.STATUS_UNSEEN) {
                props.updateProgression(step.id)
              }
            },
            render: (routeProps) => {
              const step = props.steps.find(step => routeProps.match.params.slug === step.slug)
              if (step) {
                const Current =
                  <PathCurrent
                    resourceId={props.resourceId}
                    prefix={`${props.basePath}/play`}
                    current={step}
                    all={props.steps}
                    navigation={props.navigationEnabled}
                    endPage={props.path.display.showEndPage}
                  >
                    <Step
                      {...step}
                      currentUser={props.currentUser}
                      numbering={getNumbering(props.path.display.numbering, props.path.steps, step)}
                      manualProgressionAllowed={props.path.display.manualProgressionAllowed}
                      updateProgression={props.updateProgression}
                      enableNavigation={props.enableNavigation}
                      disableNavigation={props.disableNavigation}
                      secondaryResourcesTarget={props.path.opening.secondaryResources}
                    />
                  </PathCurrent>

                return Current
              }

              routeProps.history.push(props.basePath+'/play')

              return null
            }
          }
        ]}
      />
    </Fragment>
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
  attempt: T.object,
  workspace: T.object,
  updateProgression: T.func.isRequired,
  enableNavigation: T.func.isRequired,
  disableNavigation: T.func.isRequired,
  getAttempt: T.func.isRequired
}

export {
  PlayerMain
}
