import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'

/**
 * Displays a visual stepper for iterative mode milestones.
 * Shows each milestone as a step with its completion status.
 *
 * @param {Array}    milestones     - List of milestone objects with id, title, position
 * @param {number}   currentStep    - Index of the currently active milestone
 * @param {Array}    completedSteps - Array of milestone ids that have a submission
 * @param {function} onStepClick    - Callback when a step is clicked
 */
const MilestoneStepper = ({milestones, currentStep, completedSteps, onStepClick}) => {
  if (!milestones || milestones.length === 0) {
    return null
  }

  return (
    <div className="milestone-stepper mb-4">
      <div className="d-flex align-items-center justify-content-between position-relative">
        {/* Progress line */}
        <div
          className="position-absolute bg-body-tertiary"
          style={{top: '20px', left: '20px', right: '20px', height: '2px', zIndex: 0}}
        />
        <div
          className="position-absolute bg-primary"
          style={{
            top: '20px',
            left: '20px',
            width: milestones.length > 1
              ? `${(Math.max(0, currentStep) / (milestones.length - 1)) * 100}%`
              : '0%',
            height: '2px',
            zIndex: 1,
            transition: 'width 0.3s ease'
          }}
        />

        {milestones.map((milestone, index) => {
          const isCompleted = completedSteps.includes(milestone.id)
          const isCurrent = index === currentStep
          const isPast = index < currentStep

          return (
            <div
              key={milestone.id}
              className="d-flex flex-column align-items-center position-relative"
              style={{zIndex: 2, cursor: onStepClick ? 'pointer' : 'default'}}
              onClick={() => onStepClick && onStepClick(index, milestone)}
            >
              <div
                className={classes('rounded-circle d-flex align-items-center justify-content-center', {
                  'bg-primary text-white': isCurrent || isCompleted,
                  'bg-success text-white': isCompleted && !isCurrent,
                  'bg-body-tertiary text-muted': !isCurrent && !isCompleted
                })}
                style={{width: '40px', height: '40px', fontSize: '14px', fontWeight: 'bold'}}
              >
                {isCompleted && !isCurrent ? (
                  <span className="fa fa-check" />
                ) : (
                  index + 1
                )}
              </div>
              <small
                className={classes('mt-1 text-center', {
                  'fw-bold text-primary': isCurrent,
                  'text-success': isCompleted && !isCurrent,
                  'text-muted': !isCurrent && !isCompleted
                })}
                style={{maxWidth: '100px', fontSize: '0.75em'}}
              >
                {milestone.title}
              </small>
            </div>
          )
        })}
      </div>
    </div>
  )
}

MilestoneStepper.propTypes = {
  milestones: T.arrayOf(T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired,
    position: T.number
  })).isRequired,
  currentStep: T.number,
  completedSteps: T.arrayOf(T.string),
  onStepClick: T.func
}

MilestoneStepper.defaultProps = {
  currentStep: 0,
  completedSteps: []
}

export {
  MilestoneStepper
}
