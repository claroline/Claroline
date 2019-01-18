import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {FileResource as FileResourceComponent} from '#/main/core/resources/file/components/resource'
import {reducer, selectors} from '#/main/core/resources/file/store'

const FileResource = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        url: selectors.url(state)
      })
    )(FileResourceComponent)
  )
)

export {
  FileResource
}
