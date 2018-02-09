import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {DataDetails} from '#/main/core/data/details/components/details.jsx'
import {select} from '#/main/core/data/details/selectors'

const DataDetailsContainer = connect(
  (state, ownProps) => {
    // get the root of the details in the store
    const detailsState = select.details(state, ownProps.name)

    let data = select.data(detailsState)
    if (ownProps.dataPart) {
      // just select what is related to the managed data part
      data = get(data, ownProps.dataPart)
    }

    return {
      data: data
    }
  },
  null
)(DataDetails)

DataDetailsContainer.propTypes = {
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
  DataDetailsContainer
}
