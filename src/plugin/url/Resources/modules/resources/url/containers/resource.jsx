import {withReducer} from '#/main/app/store/reducer'

import {UrlResource as UrlResourceComponent} from '#/plugin/url/resources/url/components/resource'
import {reducer, selectors} from '#/plugin/url/resources/url/store'


const UrlResource = withReducer(selectors.STORE_NAME, reducer)(
  UrlResourceComponent
)

export {
  UrlResource
}
