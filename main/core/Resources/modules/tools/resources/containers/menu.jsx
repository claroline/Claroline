import {withRouter} from '#/main/app/router'

import {ResourcesMenu as ResourcesMenuComponent} from '#/main/core/tools/resources/components/menu'

const ResourcesMenu = withRouter(
  ResourcesMenuComponent
)

export {
  ResourcesMenu
}
