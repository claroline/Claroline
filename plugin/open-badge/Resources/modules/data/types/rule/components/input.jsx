import React, {Component} from 'react'
import classes from 'classnames'

import merge from 'lodash/merge'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {Select} from '#/main/app/input/components/select'

import {InGroupInput} from '#/plugin/open-badge/data/types/rule/type/in-group/components/input'
import {InRoleInput} from '#/plugin/open-badge/data/types/rule/type/in-role/components/input'
import {ResourceCompletedAboveInput} from '#/plugin/open-badge/data/types/rule/type/resource-completed-above/components/input'
import {ResourceParticipatedInput} from '#/plugin/open-badge/data/types/rule/type/resource-participated/components/input'
import {ResourcePassedInput} from '#/plugin/open-badge/data/types/rule/type/resource-passed/components/input'
import {ResourceScoreAboveInput} from '#/plugin/open-badge/data/types/rule/type/resource-score-above/components/input'
import {WorkspaceCompletedAboveInput} from '#/plugin/open-badge/data/types/rule/type/workspace-completed-above/components/input'
import {WorkspacePassedInput} from '#/plugin/open-badge/data/types/rule/type/workspace-passed/components/input'
import {WorkspaceScoreAboveInput} from '#/plugin/open-badge/data/types/rule/type/workspace-score-above/components/input'

import {
  RESOURCE_PASSED,
  RESOURCE_SCORE_ABOVE,
  RESOURCE_COMPLETED_ABOVE,
  WORKSPACE_PASSED,
  WORKSPACE_SCORE_ABOVE,
  WORKSPACE_COMPLETED_ABOVE,
  RESOURCE_PARTICIPATED,
  IN_ROLE,
  IN_GROUP,
  PROFILE_COMPLETED
} from '#/plugin/open-badge/data/types/rule/constants'

import {trans} from '#/main/app/intl/translation'

// todo : fix responsive (incorrect margin bottom)
// todo : manages errors

class RuleInput extends Component {
  constructor(props) {
    super(props)
    this.state = {
      type: null
    }
  }

  render() {
    //the "col-md-6 col-xs-12" and the rules className for layout should be changed later
    return (
      <div className={classes('row', this.props.className)}>
        <div className="col-md-6 col-xs-12">
          <Select
            multiple={false}
            choices={
              {
                [RESOURCE_PASSED]: trans(RESOURCE_PASSED, {}, 'badge'),
                [RESOURCE_SCORE_ABOVE]: trans(RESOURCE_SCORE_ABOVE, {}, 'badge'),
                [RESOURCE_COMPLETED_ABOVE]: trans(RESOURCE_COMPLETED_ABOVE, {}, 'badge'),
                //[WORKSPACE_PASSED]: trans(WORKSPACE_PASSED),
                //[WORKSPACE_SCORE_ABOVE]: trans(WORKSPACE_SCORE_ABOVE),
                //[WORKSPACE_COMPLETED_ABOVE]: trans(WORKSPACE_COMPLETED_ABOVE),
                [RESOURCE_PARTICIPATED]: trans(RESOURCE_PARTICIPATED, {}, 'badge'),
                [IN_GROUP]: trans(IN_GROUP, {}, 'badge'),
                [IN_ROLE]: trans(IN_ROLE, {}, 'badge')
                //[PROFILE_COMPLETED]: trans(PROFILE_COMPLETED)
              }
            }
            onChange={(value) => {
              this.props.onChange({
                type: value
              })
              this.setState({
                type: value
              })
            }}
            value={this.props.value.type}
          />
        </div>
        <div>
          {(() => {
            switch(this.props.value.type) {
              case RESOURCE_PASSED:
                return <ResourcePassedInput
                  value={this.props.value.data}
                  onChange={(value) => {this.props.onChange({type: this.state.type, data: value})}}
                />
              case RESOURCE_SCORE_ABOVE:
                return <ResourceScoreAboveInput
                  value={this.props.value.data}
                  onChange={(value) => {this.props.onChange({type: this.state.type, data: merge(this.props.value.data, value)})}}
                />
              case RESOURCE_COMPLETED_ABOVE:
                return <ResourceCompletedAboveInput
                  value={this.props.value.data}
                  onChange={(value) => {this.props.onChange({type: this.state.type, data: merge(this.props.value.data, value)})}}
                />
              case WORKSPACE_PASSED:
                return <WorkspacePassedInput
                  value={this.props.value.data}
                  onChange={(value) => {this.props.onChange({type: this.state.type, data: value})}}
                />
              case WORKSPACE_SCORE_ABOVE:
                return <WorkspaceScoreAboveInput
                  value={this.props.value.data}
                  onChange={(value) => {this.props.onChange({type: this.state.type, data: merge(this.props.value.data, value)})}}
                />
              case WORKSPACE_COMPLETED_ABOVE:
                return <WorkspaceCompletedAboveInput
                  value={this.props.value.data}
                  onChange={(value) => {this.props.onChange({type: this.state.type, data: merge(this.props.value.data, value)})}}
                />
              case RESOURCE_PARTICIPATED:
                return <ResourceParticipatedInput
                  value={this.props.value.data}
                  onChange={(value) => {this.props.onChange({type: this.state.type, data: value})}}
                />
              case IN_GROUP:
                return <InGroupInput
                  value={this.props.value.data}
                  onChange={(value) => {this.props.onChange({type: this.state.type, data: value})}}
                />
              case IN_ROLE:
                return <InRoleInput
                  value={this.props.value.data}
                  onChange={(value) => {this.props.onChange({type: this.state.type, data: value})}}
                />
              case PROFILE_COMPLETED:
                return <div> PROFILE_COMPLETED </div>
            }
          }).bind(this)()}
        </div>
      </div>
    )
  }
}

implementPropTypes(RuleInput, FormFieldTypes, {
  // more precise value type
  value: T.object
}, {
  value: {
    type: '',
    data: {

    }
  }
})

export {
  RuleInput
}
