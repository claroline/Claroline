import {connect} from 'react-redux'
import {withReducer} from '#/main/app/store/reducer'

import {selectors, reducer} from '#/main/core/resources/directory/store'
import {DirectoryResource as DirectoryResourceComponent} from '#/main/core/resources/directory/components/resource'

const DirectoryResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      storageLock: selectors.storageLock(state)
    })
  )(DirectoryResourceComponent)
)

export {
  DirectoryResource
}