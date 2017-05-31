import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {t} from '#/main/core/translation'

export const ResultsPerPage = props =>
  <div className="results-per-page">
    Results per page :&nbsp;

    {props.availableSizes.map((size, index) => (
      <button
        key={index}
        className={classes(
          'btn',
          'btn-sm',
          size === props.pageSize ? 'btn-primary' : 'btn-link-default'
        )}

        onClick={e => {
          e.stopPropagation()
          props.handlePageSizeUpdate(size)
        }}
      >
        { -1 !== size ? size : t('all')}
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
