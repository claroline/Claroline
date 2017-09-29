import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {parseBool} from '#/main/core/layout/data/types/boolean/utils'

const BooleanSearch = (props) => {
  const searchValue = parseBool(props.search, true)

  return (
    <span className="boolean-filter">
      <button
        type="button"
        className={classes('btn btn-sm', {
          'btn-filter': !props.isValid || !searchValue,
          'btn-primary': props.isValid && searchValue
        })}
        onClick={() => props.updateSearch(true)}
      >
        <span className="fa fa-fw fa-check" />
      </button>

      <button
        type="button"
        className={classes('btn btn-sm', {
          'btn-filter': !props.isValid || searchValue,
          'btn-primary': props.isValid && !searchValue
        })}
        onClick={() => props.updateSearch(false)}
      >
        <span className="fa fa-fw fa-times" />
      </button>
    </span>
  )
}

BooleanSearch.propTypes = {
  search: T.string.isRequired,
  isValid: T.bool.isRequired,
  updateSearch: T.func.isRequired
}

export {BooleanSearch}
