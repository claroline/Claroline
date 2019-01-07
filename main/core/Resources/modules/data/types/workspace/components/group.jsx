import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'
import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'
import {WorkspaceInput} from '#/main/core/data/types/workspace/components/input'

const WorkspaceGroup = props =>
  <FormGroup {...props}>
    <WorkspaceInput {...props} />
  </FormGroup>

implementPropTypes(WorkspaceGroup, FormGroupWithFieldTypes, {
  value: T.shape(WorkspaceType.propTypes)
})

export {
  WorkspaceGroup
}
