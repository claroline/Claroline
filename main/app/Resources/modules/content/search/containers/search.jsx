import {PropTypes as T} from 'prop-types'

import {connect} from '#/main/app/content/search/store'
import {Search as SearchComponent} from '#/main/app/content/search/components/search'

// connect search to redux
const Search = connect()(SearchComponent)

Search.propTypes = {
  name: T.string.isRequired,
  mode: T.string.isRequired,
  available: T.arrayOf(T.shape({ // todo : use DataProp prop-types
    name: T.string.isRequired,
    options: T.object
  })).isRequired
}

export {
  Search
}
