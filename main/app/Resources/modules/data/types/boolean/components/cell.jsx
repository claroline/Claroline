import React, {Fragment} from 'react'
import classes from 'classnames'

import {DataCell as DataCellTypes} from '#/main/app/data/types/prop-types'
import {translateBool} from '#/main/app/data/types/boolean/utils'

const BooleanCell = props =>
  <Fragment>
    <span
      aria-hidden={true}
      className={classes('fa fa-fw', {
        'fa-check true': props.data,
        'fa-times false': !props.data
      })}
    />
    <span className="sr-only">{translateBool(props.data)}</span>
  </Fragment>

BooleanCell.propTypes = DataCellTypes.propTypes

export {
  BooleanCell
}
