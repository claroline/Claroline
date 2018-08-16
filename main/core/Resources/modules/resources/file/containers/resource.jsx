import {withReducer} from '#/main/app/store/components/withReducer'

import {FileResource as FileResourceComponent} from '#/main/core/resources/file/components/resource'
import {reducer, selectors} from '#/main/core/resources/file/store'

const FileResource = withReducer(selectors.STORE_NAME, reducer)(FileResourceComponent)

export {
  FileResource
}
