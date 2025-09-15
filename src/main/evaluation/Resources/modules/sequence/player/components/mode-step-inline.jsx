import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'

import {Step as StepTypes} from '#/main/evaluation/sequence/prop-types'
import {selectors} from '#/main/evaluation/sequence/store'
import {PageContent, PagePoster} from '#/main/app/page'
import {PlayerPrevious} from '#/main/evaluation/sequence/player/components/previous'
import {Step} from '#/main/evaluation/sequence/player/components/step'
import {PlayerNext} from '#/main/evaluation/sequence/player/components/next'

/**
 * Sequence player when "step" (One step and all its children per page) pagination is enabled.
 */
const PlayerModeStepInline = (props) => {
  const navigationEnabled = useSelector(selectors.navigationEnabled)

  const currentStep = useSelector(selectors.currentStep)
  const stepIndex = useSelector(selectors.currentStepIndex)

  let firstStepIndex
  if (0 === currentStep.level) {
    firstStepIndex = stepIndex
  } else {
    // find the root parent of the step
    // it's the first level 0 step when going backward in the tree
    firstStepIndex = (props.steps.length - 1 - props.steps.slice().reverse().findIndex((s, i) => i > (props.steps.length - 1 - stepIndex) && 0 === s.level))
  }

  let nextStepIndex
  if (firstStepIndex + 1 < props.steps.length) {
    nextStepIndex = props.steps.findIndex((s, i) => i > stepIndex && 0 === s.level)
  }

  let previous
  if (0 !== firstStepIndex) {
    previous = props.steps.slice().reverse().find((s, i) => i > (props.steps.length - 1 - firstStepIndex) && 0 === s.level)
  }
  let next
  if (-1 !== nextStepIndex) {
    next = props.steps[nextStepIndex]
  }

  return (
    <PageContent className="d-flex flex-column">
      <PlayerPrevious
        className="rounded-0 px-0 shadow-none fs-lg"
        path={props.path}
        previous={previous}
      />

      <div role="presentation" className="h-100 flex-shrink-0 d-flex flex-column">
        {props.steps
          .slice(firstStepIndex, -1 !== nextStepIndex ? nextStepIndex : undefined)
          .map((step, stepIndex) => (
            <div key={step.id} id={'step-'+step.id} role="presentation" className="d-flex flex-column flex-fill">
              {(0 === stepIndex && step.poster) &&
                <PagePoster poster={step.poster} />
              }
              <Step
                level={step.level}
                poster={0 !== stepIndex}
                title={0 === stepIndex}
                step={step}
                updateProgression={props.updateProgression}
                enableNavigation={props.enableNavigation}
                disableNavigation={props.disableNavigation}
              />
            </div>
          ))
        }

        {navigationEnabled &&
          <PlayerNext
            className="rounded-0 px-0 shadow-none fs-lg"
            path={props.path}
            next={next}
          />
        }
      </div>
    </PageContent>
  )
}

PlayerModeStepInline.propTypes = {
  path: T.string.isRequired,
  steps: T.arrayOf(T.shape(
    StepTypes.propTypes
  )).isRequired,
  updateProgression: T.func.isRequired,
  enableNavigation: T.func.isRequired,
  disableNavigation: T.func.isRequired
}

export {
  PlayerModeStepInline
}
