import React, { PropTypes as T } from 'react'
import classes from 'classnames'

import {tex} from './../../../utils/translate'

export const ResultsPerPage = props =>
  <div className="results-per-page">
    Results per page :&nbsp;

    {props.availableSizes.map((size, index) => (
      <button
        key={index}
        className={classes(
          'btn',
          'btn-sm',
          size === props.pageSize ? 'btn-primary' : 'btn-link'
        )}

        onClick={e => {
          e.stopPropagation()
          props.handlePageSizeUpdate(size)
        }}
      >
        { -1 !== size ? size : tex('all')}
      </button>
    ))}
  </div>

ResultsPerPage.propTypes = {
  availableSizes: T.arrayOf(T.number),
  pageSize: T.number,
  handlePageSizeUpdate: T.func.isRequired
}

ResultsPerPage.defaultProps = {
  availableSizes: [10, 20, 50, 100, -1],
  pageSize: 20
}
