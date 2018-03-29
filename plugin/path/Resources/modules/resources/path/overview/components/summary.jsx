import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'

const SummaryStep = props =>
  <li>
    <a className="summary-link" href={`#/play/${props.step.id}`} role="link">
      {props.step.title}
    </a>

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
