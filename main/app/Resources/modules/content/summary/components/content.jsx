import React from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Summary} from '#/main/app/content/summary/components/summary'

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
  summary: T.shape({
    displayed: T.bool.isRequired,
    opened: T.bool,
    pinned: T.bool,
    title: T.string,
    links: T.arrayOf(T.shape(merge({}, ActionTypes.propTypes, {
      additional: T.arrayOf(T.shape(
        ActionTypes.propTypes
      )),
      // TODO : find a way to document more nesting
      children: T.arrayOf(T.shape(
        ActionTypes.propTypes
      ))
    })))
  }).isRequired,
  children: T.any.isRequired
}

export {
  SummarizedContent
}
