import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {LinkButton} from '#/main/app/buttons'
import {DataMicro} from '#/main/app/data/components/micro'

const SearchResults = (props) => {
  if (props.empty) {
    return (
      <div className="text-center mt-4" role="presentation">
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
          <div role="presentation" className="mt-4" key={resultType}>
            <h5 className="fs-sm text-uppercase text-body-secondary">{props.availableSearches[resultType].label}</h5>
            <ul className="list-unstyled mb-0 border-bottom">
              {props.results[resultType].map((result) =>
                <li key={result.id} className="position-relative">
                  <LinkButton
                    className="d-block w-100 text-reset py-2 pe-3 border-top"
                    target={props.availableSearches[resultType].link(result)}
                    onClick={() => {
                      props.reset()
                      props.fadeModal()
                    }}
                  >
                    {props.availableSearches[resultType].component ?
                      createElement(props.availableSearches[resultType].component, {
                        object: result
                      }) :
                      <DataMicro object={result} />
                    }
                  </LinkButton>
                  <span className="fa fa-chevron-right text-body-tertiary position-absolute end-0 top-50 translate-middle" aria-hidden={true} />
                </li>
              )}
            </ul>
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
