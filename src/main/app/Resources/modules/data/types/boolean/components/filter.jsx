import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/app/data/types/prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {parseBool} from '#/main/app/data/types/boolean/utils'

const BooleanFilter = (props) => {
  const searchValue = parseBool(props.search, true)

  return (
    <span className="data-filter boolean-filter">
      <Button
        className={classes('btn btn-filter', {
          'btn-primary': props.isValid && searchValue,
          'btn-body': !props.isValid || !searchValue
        })}
        type={CALLBACK_BUTTON}
        label={trans('yes')}
        callback={() => props.updateSearch(true)}
        disabled={props.disabled}
        size={props.size}
      />

      <Button
        className={classes('btn btn-filter', {
          'btn-primary': props.isValid && !searchValue,
          'btn-body': !props.isValid || searchValue
        })}
        type={CALLBACK_BUTTON}
        label={trans('no')}
        callback={() => props.updateSearch(false)}
        disabled={props.disabled}
        size={props.size}
      />
    </span>
  )
}

implementPropTypes(BooleanFilter, DataSearchTypes, {
  search: T.oneOfType([T.string, T.bool])
})

export {
  BooleanFilter
}
