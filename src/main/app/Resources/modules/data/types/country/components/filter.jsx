import React from 'react'

import {implementPropTypes} from '#/main/app/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/app/data/types/prop-types'

import {ChoiceSearch} from '#/main/app/data/types/choice/components/search'
import {constants as intlConstants} from '#/main/app/intl/constants'

const CountryFilter = (props) =>
  <ChoiceSearch
    {...props}
    choices={intlConstants.REGIONS}
  />

implementPropTypes(CountryFilter, DataSearchTypes)

export {
  CountryFilter
}
