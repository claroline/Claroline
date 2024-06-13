import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'

const SearchResults = (props) => {
  if (props.empty) {
    return (
      <div className="text-center mt-3">
        <p className="lead mb-1">{trans('no_search_results')}</p>
        <p className="mb-0 text-secondary">{trans('no_search_results_help')}</p>
      </div>
    )
  }

  return (
    <>
      {Object.keys(props.results)
        .filter(resultType => !isEmpty(props.availableSearches[resultType]) && !isEmpty(props.results[resultType]))
        .map(resultType =>
          <div role="presentation" className="mt-3" key={resultType}>
            <h5 className="fs-sm text-uppercase text-body-secondary">{props.availableSearches[resultType].label}</h5>
            <div className="d-flex flex-column gap-1">
              {props.results[resultType].map(result =>
                createElement(props.availableSearches[resultType].component, {
                  key: result.id,
                  size: 'sm',
                  direction: 'row',
                  data: result,
                  primaryAction: {
                    type: LINK_BUTTON,
                    label: trans('open', {}, 'actions'),
                    target: props.availableSearches[resultType].link(result),
                    onClick: () => {
                      props.reset()
                      props.fadeModal()
                    }
                  }
                })
              )}
            </div>
          </div>
        )}
    </>
  )
}

SearchResults.propTypes = {
  results: T.object,
  empty: T.bool.isRequired,
  availableSearches: T.object.isRequired,
  reset: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  SearchResults
}
