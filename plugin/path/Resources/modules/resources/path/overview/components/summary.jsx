import React from 'react'
import {PropTypes as T} from 'prop-types'

import {scrollTo} from '#/main/app/dom/scroll'
import {LinkButton} from '#/main/app/buttons/link/components/button'

import {Step as StepTypes} from '#/plugin/path/resources/path/prop-types'

const SummaryStep = props =>
  <li>
    <LinkButton
      className="summary-link"
      target={`${props.basePath}/play/${props.step.slug}`}
      onClick={() => scrollTo(`#resource-${props.resourceId} > .page-content`)}
    >
      {props.step.title}
    </LinkButton>

    {0 !== props.step.children.length &&
      <ul>
        {props.step.children.map(child =>
          <SummaryStep
            key={child.id}
            basePath={props.basePath}
            step={child}
          />
        )}
      </ul>
    }
  </li>

SummaryStep.propTypes = {
  basePath: T.string.isRequired,
  resourceId: T.string.isRequired,
  step: T.shape(
    StepTypes.propTypes
  ).isRequired
}

const OverviewSummary = props =>
  <ul className="summary-overview">
    {props.steps.map(step =>
      <SummaryStep
        key={step.id}
        basePath={props.basePath}
        step={step}
      />
    )}
  </ul>

OverviewSummary.propTypes = {
  basePath: T.string.isRequired,
  resourceId: T.string.isRequired,
  steps: T.arrayOf(T.shape(
    StepTypes.propTypes
  )).isRequired
}

export {
  OverviewSummary
}
