import React from 'react'

import {implementPropTypes} from '#/main/app/prop-types'

import {SearchFull} from '#/main/app/content/search/components/full'
import {SearchUnified} from '#/main/app/content/search/components/unified'
import {Search as SearchTypes} from '#/main/app/content/search/prop-types'
import {constants} from '#/main/app/content/search/constants'

const Search = props => {
  switch (props.mode) {
    case constants.SEARCH_FULL:
      return (
        <SearchFull {...props} />
      )

    case constants.SEARCH_UNIFIED:
      return (
        <SearchUnified  {...props} />
      )
  }
}

implementPropTypes(Search, SearchTypes)

export {
  Search
}
