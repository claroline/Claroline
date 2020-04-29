import {withRouter} from '#/main/app/router'

import {UrlResource as UrlResourceComponent} from '#/plugin/url/resources/url/components/resource'

const UrlResource = withRouter(
  UrlResourceComponent
)

export {
  UrlResource
}
