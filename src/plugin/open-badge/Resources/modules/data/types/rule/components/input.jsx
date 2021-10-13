import React from 'react'
import classes from 'classnames'
import merge from 'lodash/merge'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {FormGroup} from '#/main/app/content/form/components/group'
import {Select} from '#/main/app/input/components/select'

import {InGroupInput} from '#/plugin/open-badge/data/types/rule/type/in-group/components/input'
import {InRoleInput} from '#/plugin/open-badge/data/types/rule/type/in-role/components/input'
import {ResourceCompletedAboveInput} from '#/plugin/open-badge/data/types/rule/type/resource-completed-above/components/input'
import {ResourcePassedInput} from '#/plugin/open-badge/data/types/rule/type/resource-passed/components/input'
import {ResourceScoreAboveInput} from '#/plugin/open-badge/data/types/rule/type/resource-score-above/components/input'
import {WorkspaceCompletedAboveInput} from '#/plugin/open-badge/data/types/rule/type/workspace-completed-above/components/input'
import {WorkspacePassedInput} from '#/plugin/open-badge/data/types/rule/type/workspace-passed/components/input'
import {WorkspaceScoreAboveInput} from '#/plugin/open-badge/data/types/rule/type/workspace-score-above/components/input'

import {
  RESOURCE_STATUS,
  RESOURCE_SCORE_ABOVE,
  RESOURCE_COMPLETED_ABOVE,
  WORKSPACE_STATUS,
  WORKSPACE_SCORE_ABOVE,
  WORKSPACE_COMPLETED_ABOVE,
  IN_ROLE,
  IN_GROUP
} from '#/plugin/open-badge/data/types/rule/constants'

import {trans} from '#/main/app/intl/translation'

// todo : fix responsive (incorrect margin bottom)
// todo : manages errors

const RuleDataInput = (props) => {
  switch (props.type) {
    case RESOURCE_STATUS:
      return (
        <ResourcePassedInput
          id={props.id}
          disabled={props.disabled}
          value={props.value}
          onChange={(value) => props.onChange(merge(props.value || {}, value))}
          size={props.size}
        />
      )

    case RESOURCE_SCORE_ABOVE:
      return (
        <ResourceScoreAboveInput
          id={props.id}
          disabled={props.disabled}
          value={props.value}
          onChange={(value) => props.onChange(merge(props.value || {}, value))}
          size={props.size}
        />
      )

    case RESOURCE_COMPLETED_ABOVE:
      return (
        <ResourceCompletedAboveInput
          id={props.id}
          disabled={props.disabled}
          value={props.value}
          onChange={(value) => props.onChange(merge(props.value || {}, value))}
          size={props.size}
        />
      )

    case WORKSPACE_STATUS:
      return (
        <WorkspacePassedInput
          id={props.id}
          disabled={props.disabled}
          value={props.value}
          onChange={(value) => props.onChange(merge(props.value || {}, value))}
          size={props.size}
        />
      )

    case WORKSPACE_SCORE_ABOVE:
      return (
        <WorkspaceScoreAboveInput
          id={props.id}
          disabled={props.disabled}
          value={props.value}
          onChange={(value) => props.onChange(merge(props.value || {}, value))}
          size={props.size}
        />
      )

    case WORKSPACE_COMPLETED_ABOVE:
      return (
        <WorkspaceCompletedAboveInput
          id={props.id}
          disabled={props.disabled}
          value={props.value}
          onChange={(value) => props.onChange(merge(props.value || {}, value))}
          size={props.size}
        />
      )

    case IN_GROUP:
      return (
        <InGroupInput
          id={props.id}
          disabled={props.disabled}
          value={props.value}
          onChange={props.onChange}
          size={props.size}
        />
      )

    case IN_ROLE:
      return (
        <InRoleInput
          id={props.id}
          disabled={props.disabled}
          value={props.value}
          onChange={props.onChange}
          size={props.size}
        />
      )
  }
}

RuleDataInput.propTypes = {
  id: T.string.isRequired,
  type: T.string.isRequired,
  disabled: T.bool,
  value: T.any,
  size: T.string,
  onChange: T.func.isRequired
}

const RuleInput = (props) =>
  <div className={classes('rule-control', props.className)}>
    <FormGroup
      id={`${props.id}-rule-type`}
      label={trans('type')}
      hideLabel={true}
    >
      <Select
        id={`${props.id}-rule-type`}
        disabled={props.disabled}
        choices={{
          // resources
          [RESOURCE_STATUS]: trans(RESOURCE_STATUS, {}, 'badge'),
          [RESOURCE_SCORE_ABOVE]: trans(RESOURCE_SCORE_ABOVE, {}, 'badge'),
          [RESOURCE_COMPLETED_ABOVE]: trans(RESOURCE_COMPLETED_ABOVE, {}, 'badge'),
          // workspaces
          [WORKSPACE_STATUS]: trans(WORKSPACE_STATUS, {}, 'badge'),
          [WORKSPACE_SCORE_ABOVE]: trans(WORKSPACE_SCORE_ABOVE, {}, 'badge'),
          [WORKSPACE_COMPLETED_ABOVE]: trans(WORKSPACE_COMPLETED_ABOVE, {}, 'badge'),
          // users
          [IN_GROUP]: trans(IN_GROUP, {}, 'badge'),
          [IN_ROLE]: trans(IN_ROLE, {}, 'badge')
        }}
        onChange={(value) => props.onChange({type: value, data: null})}
        value={props.value.type}
        size={props.size}
      />
    </FormGroup>

    {props.value.type &&
      <div className="sub-fields">
        <RuleDataInput
          id={`${props.id}-rule-data`}
          type={props.value.type}
          value={props.value.data}
          disabled={props.disabled}
          size={props.size}
          onChange={(value) => props.onChange({type: props.value.type, data: value})}
        />
      </div>
    }
  </div>

implementPropTypes(RuleInput, DataInputTypes, {
  // more precise value type
  value: T.shape({
    type: T.string,
    data: T.any
  })
}, {
  value: {
    type: '',
    data: {}
  }
})

export {
  RuleInput
}
