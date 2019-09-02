import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Step as StepTypes} from '#/plugin/exo/resources/quiz/prop-types'
import {PlayerStep} from '#/plugin/exo/resources/quiz/player/components/step'
import {PlayerEnd} from '#/plugin/exo/resources/quiz/player/components/end'

const PlayerMain = (props) =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/:slug',
        component: PlayerStep,
        render: (routeProps) => {
          const stepIndex = props.steps.findIndex(step => routeProps.match.params.slug === step.slug)
          if (-1 !== stepIndex) {
            const currentStep = props.steps[stepIndex]

            const Step = (
              <PlayerStep
                index={stepIndex}
                numbering={props.numberingType}
                title={currentStep.title}
                description={currentStep.description}
                items={currentStep.items}
              />
            )

            return Step
          }

          routeProps.history.push(props.path)

          return null
        }
      }, {
        path: '/end',
        component: PlayerEnd
      }
    ]}
  />

PlayerMain.propTypes = {
  path: T.string.isRequired,
  numberingType: T.string,
  steps: T.arrayOf(T.shape(
    StepTypes.propTypes
  ))
}

export {
  PlayerMain
}
