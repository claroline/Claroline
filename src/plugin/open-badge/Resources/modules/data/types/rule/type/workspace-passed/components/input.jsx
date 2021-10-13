import React, {Fragment} from 'react'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'
import {ChoiceInput} from '#/main/app/data/types/choice/components/input'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {WorkspaceInput} from '#/main/core/data/types/workspace/components/input'
import {constants} from '#/main/core/workspace/constants'

// todo : manages errors

const WorkspacePassedInput = (props) =>
  <Fragment>
    <FormGroup
      id={props.id}
      label={trans('workspace')}
    >
      <WorkspaceInput
        {...props}
        onChange={(value) => props.onChange({workspace: value})}
        value={get(props.value, 'workspace')}
      />
    </FormGroup>

    <FormGroup
      id={`${props.id}-status`}
      className="form-last"
      label={trans('status')}
    >
      <ChoiceInput
        id={`${props.id}-status`}
        disabled={props.disabled}
        onChange={(value) => props.onChange({value: value})}
        value={get(props.value, 'value')}
        size={props.size}
        choices={constants.EVALUATION_STATUSES}
      />
    </FormGroup>
  </Fragment>

implementPropTypes(WorkspacePassedInput, DataInputTypes, {
  // more precise value type
  value: T.shape({
    workspace: WorkspaceTypes.propTypes,
    value: T.string
  })
}, {
  value: {
    workspace: null,
    value: null
  }
})

export {
  WorkspacePassedInput
}
