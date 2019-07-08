import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {DirectoryResource as DirectoryResourceComponent} from '#/main/core/resources/directory/components/resource'

const DirectoryResource = withRouter(
  connect(

  )(DirectoryResourceComponent)
)

export {
  DirectoryResource
}
