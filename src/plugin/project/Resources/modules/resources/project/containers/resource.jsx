import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'

import {ProjectResource as ProjectResourceComponent} from '#/plugin/project/resources/project/components/resource'
import {reducer, selectors} from '#/plugin/project/resources/project/store'

const ProjectResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
      project: selectors.project(state),
      mySubmissions: selectors.mySubmissions(state),
      myCorrections: selectors.myCorrections(state)
    })
  )(ProjectResourceComponent)
)

export {
  ProjectResource
}
