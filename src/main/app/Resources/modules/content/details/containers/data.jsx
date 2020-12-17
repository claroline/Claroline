import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {DetailsData as DetailsDataComponent} from '#/main/app/content/details/components/data'
import {selectors} from '#/main/app/content/details/store'

const DetailsData = connect(
  (state, ownProps) => {
    // get the root of the details in the store
    const detailsState = selectors.details(state, ownProps.name)

    let data = selectors.data(detailsState)
    if (ownProps.dataPart) {
      // just select what is related to the managed data part
      data = get(data, ownProps.dataPart)
    }

    return {
      data: data
    }
  },
  null
)(DetailsDataComponent)

DetailsData.propTypes = {
  /**
   * The name of the data in the form.
   *
   * It should be the key in the store where the list has been mounted
   * (aka where `makeFormReducer()` has been called).
   */
  name: T.string.isRequired,

  /**
   * Permits to connect the details on a sub-part of the data.
   * This is useful when the details are broken in multiple steps/pages
   *
   * It MUST be a valid lodash/get selector.
   */
  dataPart: T.string
}

export {
  DetailsData
}
