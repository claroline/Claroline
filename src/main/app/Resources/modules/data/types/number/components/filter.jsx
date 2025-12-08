import React from 'react'

import {implementPropTypes, PropTypes as T} from '#/main/app/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/app/data/types/prop-types'

import {NumberInput} from '#/main/app/data/types/number/components/input'

const NumberFilter = (props) => {
  return (
    <NumberInput
      {...props}
      onChange={(v) => props.updateSearch(v)}
    />
  )
}

implementPropTypes(NumberFilter, DataSearchTypes, {
  search: T.number
})

export {
  NumberFilter
}
