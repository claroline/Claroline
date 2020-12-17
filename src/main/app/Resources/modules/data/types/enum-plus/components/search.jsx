import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/app/data/types/prop-types'

import {Select} from '#/main/app/data/types/enum-plus/components/select'

// TODO : merge with Select component

const EnumPlusSearch = (props) =>
  <Select
    className="enum-filter"
    choices={props.choices}
    onChange={props.updateSearch}
    transDomain={props.transDomain}
    value={props.search}
    isValid={props.isValid}
    searchable={true}
  />

implementPropTypes(EnumPlusSearch, DataSearchTypes, {
  choices: T.array.isRequired,
  transDomain: T.string
}, {
  transDomain: null
})

export {
  EnumPlusSearch
}
