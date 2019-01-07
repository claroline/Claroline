import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'
import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'
import {WorkspacesInput} from '#/main/core/data/types/workspaces/components/input'

const WorkspacesGroup = props =>
  <FormGroup {...props}>
    <WorkspacesInput {...props} />
  </FormGroup>

implementPropTypes(WorkspacesGroup, FormGroupWithFieldTypes, {
  value: T.arrayOf(T.shape(WorkspaceType.propTypes))
})

export {
  WorkspacesGroup
}
