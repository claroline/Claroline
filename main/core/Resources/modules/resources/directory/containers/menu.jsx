import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {DirectoryMenu as DirectoryMenuComponent} from '#/main/core/resources/directory/components/menu'

const DirectoryMenu = withRouter(
  connect(

  )(DirectoryMenuComponent)
)

export {
  DirectoryMenu
}
