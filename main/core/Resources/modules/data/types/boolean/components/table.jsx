import React from 'react'
import classes from 'classnames'

import {DataCell as DataCellTypes} from '#/main/core/data/prop-types'
import {translateBool} from '#/main/core/data/types/boolean/utils'

const BooleanCell = props =>
  <span>
    <span
      aria-hidden={true}
      className={classes('fa fa-fw', {
        'fa-check true': props.data,
        'fa-times false': !props.data
      })}
    />
    <span className="sr-only">{translateBool(props.data)}</span>
  </span>

BooleanCell.propTypes = DataCellTypes.propTypes

export {
  BooleanCell
}
