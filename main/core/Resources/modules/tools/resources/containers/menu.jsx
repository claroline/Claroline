import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {ResourcesMenu as ResourcesMenuComponent} from '#/main/core/tools/resources/components/menu'
import {selectors} from '#/main/core/tools/home/store'

const ResourcesMenu = withRouter(
  connect(
    (state) => ({

    })
  )(ResourcesMenuComponent)
)

export {
  ResourcesMenu
}
