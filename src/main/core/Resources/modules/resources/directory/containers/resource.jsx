import {connect} from 'react-redux'

import {selectors} from '#/main/core/resources/directory/store'
import {DirectoryResource as DirectoryResourceComponent} from '#/main/core/resources/directory/components/resource'

const DirectoryResource = connect(
  (state) => ({
    storageLock: selectors.storageLock(state)
  })
)(DirectoryResourceComponent)

export {
  DirectoryResource
}