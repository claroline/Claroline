import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Step as StepTypes} from '#/plugin/exo/resources/quiz/prop-types'
import {PlayerStep} from '#/plugin/exo/resources/quiz/player/components/step'
import {PlayerEnd} from '#/plugin/exo/resources/quiz/player/components/end'

const PlayerMain = (props) =>
  <Routes
    routes={[
      {
        path: '/:id',
        component: PlayerStep,
        render: (routeProps) => {
          const stepIndex = props.steps.findIndex(step => routeProps.match.params.id === step.id)
          if (-1 !== stepIndex) {
            const currentStep = props.steps[stepIndex]

            return (
              <PlayerStep
                index={stepIndex}
                numbering={props.numberingType}
                title={currentStep.title}
                description={currentStep.description}
                items={currentStep.items}
              />
            )
          }

          routeProps.history.push('/')

          return null
        }
      }, {
        path: '/end',
        component: PlayerEnd
      }
    ]}
  />

PlayerMain.propTypes = {
  numberingType: T.string,
  steps: T.arrayOf(T.shape(
    StepTypes.propTypes
  )),
}

export {
  PlayerMain
}
