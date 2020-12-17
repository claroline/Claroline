import {PropTypes as T} from 'prop-types'

import {connect} from '#/main/app/content/search/store'
import {SearchFull as SearchFullComponent} from '#/main/app/content/search/components/full'

// connect search to redux
const SearchFull = connect()(SearchFullComponent)

SearchFull.propTypes = {
  name: T.string.isRequired,
  available: T.arrayOf(T.shape({ // todo : use DataProp prop-types
    name: T.string.isRequired,
    options: T.object
  })).isRequired
}

export {
  SearchFull
}
