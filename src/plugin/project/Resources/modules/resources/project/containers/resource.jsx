import {withReducer} from '#/main/app/store/reducer'

import {ProjectResource as ProjectResourceComponent} from '#/plugin/project/resources/project/components/resource'
import {reducer, selectors} from '#/plugin/project/resources/project/store'

const ProjectResource = withReducer(selectors.STORE_NAME, reducer)(
  ProjectResourceComponent
)

export {
  ProjectResource
}
