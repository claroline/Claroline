import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {Routes} from '#/main/app/router'
import {currentUser} from '#/main/core/user/current'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

import {selectors} from '#/plugin/path/resources/path/store'

import {constants} from '#/plugin/path/resources/path/constants'
import {Path as PathTypes, Step as StepTypes} from '#/plugin/path/resources/path/prop-types'
import {PathCurrent} from '#/plugin/path/resources/path/components/current'
import {Step} from '#/plugin/path/resources/path/player/components/step'
import {PathSummary} from '#/plugin/path/resources/path/components/summary'
import {getNumbering, flattenSteps, getStepUserProgression} from '#/plugin/path/resources/path/utils'
import {actions} from '#/plugin/path/resources/path/store'

const authenticatedUser = currentUser()

// todo manage empty steps
const PlayerComponent = props =>
  <div>
    {0 === props.steps.length &&
      <EmptyPlaceholder
        size="lg"
        title={trans('no_step', {}, 'path')}
      />
    }
    {0 !== props.steps.length &&
    <section className="summarized-content">
      <h2 className="sr-only">{trans('play')}</h2>

      {props.path.display.showSummary &&
      <PathSummary
        prefix="play"
        steps={props.path.steps}
      />
      }

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
                />
              </PathCurrent>

              return Current
            }
          }
        ]}
      />
    </section>
    }
  </div>

PlayerComponent.propTypes = {
  fullWidth: T.bool.isRequired,
  navigationEnabled: T.bool.isRequired,
  path: T.shape(
    PathTypes.propTypes
  ).isRequired,
  steps: T.arrayOf(T.shape(
    StepTypes.propTypes
  )),
  updateProgression: T.func.isRequired,
  enableNavigation: T.func.isRequired,
  disableNavigation: T.func.isRequired
}

const Player = connect(
  state => ({
    path: selectors.path(state),
    navigationEnabled: selectors.navigationEnabled(state),
    fullWidth: selectors.fullWidth(state),
    steps: flattenSteps(selectors.steps(state))
  }),
  dispatch => ({
    updateProgression(stepId, status = constants.STATUS_SEEN) {
      dispatch(actions.updateProgression(stepId, status))
    },
    enableNavigation() {
      dispatch(actions.enableNavigation())
    },
    disableNavigation() {
      dispatch(actions.disableNavigation())
    }
  })
)(PlayerComponent)

export {
  Player
}
