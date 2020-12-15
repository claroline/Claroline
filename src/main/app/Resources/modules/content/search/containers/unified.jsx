import {PropTypes as T} from 'prop-types'

import {connect} from '#/main/app/content/search/store'
import {SearchUnified as SearchUnifiedComponent} from '#/main/app/content/search/components/unified'

// connect search to redux
const SearchUnified = connect()(SearchUnifiedComponent)

SearchUnified.propTypes = {
  name: T.string.isRequired,
  available: T.arrayOf(T.shape({ // todo : use DataProp prop-types
    name: T.string.isRequired,
    options: T.object
  })).isRequired
}

export {
  SearchUnified
}
