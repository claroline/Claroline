import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {WorkspaceInput} from '#/main/core/data/types/workspace/components/input'

// todo : manages errors

const WorkspacePassedInput = (props) =>
  <FormGroup
    id={props.id}
    className="form-last"
    label={trans('workspace')}
  >
    <WorkspaceInput {...props} />
  </FormGroup>

implementPropTypes(WorkspacePassedInput, FormFieldTypes, {
  // more precise value type
  value: T.shape(
    WorkspaceTypes.propTypes
  )
}, {
  value: null
})

export {
  WorkspacePassedInput
}
