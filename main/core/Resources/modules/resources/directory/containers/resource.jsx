import {withReducer} from '#/main/app/store/components/withReducer'

import {DirectoryResource as DirectoryResourceComponent} from '#/main/core/resources/directory/components/resource'
import {reducer, selectors} from '#/main/core/resources/directory/store'

const DirectoryResource = withReducer(selectors.STORE_NAME, reducer)(DirectoryResourceComponent)

export {
  DirectoryResource
}
