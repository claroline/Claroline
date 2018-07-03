import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Summary} from '#/main/app/content/summary/components/summary'
import {Summary as SummaryTypes} from '#/main/app/content/summary/prop-types'

const SummarizedContent = props =>
  <section className="summarized-content">
    {props.summary.displayed &&
      <Summary
        {...props.summary}
      />
    }

    <div className="content-container">
      {props.children}
    </div>
  </section>

SummarizedContent.propTypes = {
  summary: T.shape(
    SummaryTypes.propTypes
  ).isRequired,
  children: T.any.isRequired
}

export {
  SummarizedContent
}
