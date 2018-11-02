import React from 'react'
import {PropTypes as T} from 'prop-types'

import {LinkButton} from '#/main/app/buttons/link/components/button'

import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'

const SummaryStep = props =>
  <li>
    <LinkButton
      className="summary-link"
      target={`/play/${props.step.id}`}
    >
      {props.step.title}
    </LinkButton>

    {0 !== props.step.children.length &&
      <ul>
        {props.step.children.map(child =>
          <SummaryStep key={child.id} step={child} />
        )}
      </ul>
    }
  </li>

SummaryStep.propTypes = {
  step: T.shape(
    StepTypes.propTypes
  ).isRequired
}

const Summary = props =>
  <ul className="summary-overview">
    {props.steps.map(step =>
      <SummaryStep key={step.id} step={step} />
    )}
  </ul>

Summary.propTypes = {
  steps: T.arrayOf(T.shape(
    StepTypes.propTypes
  )).isRequired
}

export {
  Summary
}
