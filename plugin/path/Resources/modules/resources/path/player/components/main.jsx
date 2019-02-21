import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {currentUser} from '#/main/app/security'
import {LINK_BUTTON} from '#/main/app/buttons'
import {SummarizedContent} from '#/main/app/content/summary/components/content'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

import {constants} from '#/plugin/path/resources/path/constants'
import {Path as PathTypes, Step as StepTypes} from '#/plugin/path/resources/path/prop-types'
import {PathCurrent} from '#/plugin/path/resources/path/components/current'
import {Step} from '#/plugin/path/resources/path/player/components/step'
import {getNumbering, getStepUserProgression} from '#/plugin/path/resources/path/utils'

const authenticatedUser = currentUser()

function getStepSummary(step) {
  return {
    type: LINK_BUTTON,
    icon: classes('step-progression fa fa-circle', step.userProgression && step.userProgression.status),
    label: step.title,
    target: `/play/${step.id}`,
    children: step.children ? step.children.map(getStepSummary) : []
  }
}

// todo manage empty steps
const PlayerMain = props => {
  if (0 === props.steps.length) {
    return (
      <EmptyPlaceholder
        size="lg"
        title={trans('no_step', {}, 'path')}
      />
    )
  }

  return (
    <Fragment>
      <h2 className="sr-only">{trans('play')}</h2>
      <SummarizedContent
        summary={{
          displayed: props.path.display.showSummary,
          opened: props.summaryOpened,
          pinned: props.summaryPinned,
          links: props.path.steps.map(getStepSummary)
        }}
      >
        <Routes
          redirect={[
            {from: '/play', to: `/play/${props.steps[0].id}`}
          ]}
          routes={[
            {
              path: '/play/:id',
              onEnter: (params) => {
                if (authenticatedUser && getStepUserProgression(props.steps, params.id) === constants.STATUS_UNSEEN) {
                  props.updateProgression(params.id)
                }
              },
              render: (routeProps) => {
                const step = props.steps.find(step => routeProps.match.params.id === step.id)
                const Current =
                  <PathCurrent
                    prefix="/play"
                    current={step}
                    all={props.steps}
                    navigation={props.navigationEnabled}
                  >
                    <Step
                      {...step}
                      fullWidth={props.fullWidth}
                      numbering={getNumbering(props.path.display.numbering, props.path.steps, step)}
                      manualProgressionAllowed={props.path.display.manualProgressionAllowed}
                      updateProgression={props.updateProgression}
                      enableNavigation={props.enableNavigation}
                      disableNavigation={props.disableNavigation}
                      onEmbeddedResourceClose={props.computeResourceDuration}
                      secondaryResourcesTarget={props.path.opening.secondaryResources}
                    />
                  </PathCurrent>

                return Current
              }
            }
          ]}
        />
      </SummarizedContent>
    </Fragment>
  )
}

PlayerMain.propTypes = {
  fullWidth: T.bool.isRequired,
  // summary
  summaryOpened: T.bool.isRequired,
  summaryPinned: T.bool.isRequired,
  navigationEnabled: T.bool.isRequired,
  path: T.shape(
    PathTypes.propTypes
  ).isRequired,
  steps: T.arrayOf(T.shape(
    StepTypes.propTypes
  )),
  updateProgression: T.func.isRequired,
  enableNavigation: T.func.isRequired,
  disableNavigation: T.func.isRequired,
  computeResourceDuration: T.func.isRequired
}

export {
  PlayerMain
}
