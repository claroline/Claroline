import React, {Fragment} from 'react'
import isEmpty from 'lodash/isEmpty'

import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {route} from '#/main/core/workspace/routing'
import {WorkspaceCard} from '#/main/core/workspace/components/card'
import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'
import {MODAL_WORKSPACES} from '#/main/core/modals/workspaces'

const WorkspacesButton = props =>
  <Button
    className="btn btn-block"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-plus"
    label={trans('add_workspaces')}
    disabled={props.disabled}
    modal={[MODAL_WORKSPACES, {
      url: ['apiv2_workspace_list_managed'],
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected)
      })
    }]}
    size={props.size}
  />

WorkspacesButton.propTypes = {
  title: T.string,
  disabled: T.bool,
  onChange: T.func.isRequired,
  size: T.string
}

const WorkspacesInput = props => {
  if (!isEmpty(props.value)) {
    return (
      <Fragment>
        {props.value.map(workspace =>
          <WorkspaceCard
            key={`workspace-card-${workspace.id}`}
            data={workspace}
            size="xs"
            primaryAction={{
              type: LINK_BUTTON,
              label: trans('open', {}, 'actions'),
              target: route(workspace)
            }}
            actions={[
              {
                name: 'delete',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-trash',
                label: trans('delete', {}, 'actions'),
                dangerous: true,
                disabled: props.disabled,
                callback: () => {
                  const newValue = props.value
                  const index = newValue.findIndex(u => u.id === workspace.id)

                  if (-1 < index) {
                    newValue.splice(index, 1)
                    props.onChange(newValue)
                  }
                }
              }
            ]}
          />
        )}

        <WorkspacesButton
          {...props.picker}
          disabled={props.disabled}
          size={props.size}
          onChange={(selected) => {
            const newValue = props.value
            selected.forEach(workspace => {
              const index = newValue.findIndex(u => u.id === workspace.id)

              if (-1 === index) {
                newValue.push(workspace)
              }
            })

            props.onChange(newValue)
          }}
        />
      </Fragment>
    )
  }

  return (
    <ContentPlaceholder
      icon="fa fa-book"
      title={trans('no_workspace')}
      size={props.size}
    >
      <WorkspacesButton
        {...props.picker}
        disabled={props.disabled}
        onChange={props.onChange}
        size={props.size}
      />
    </ContentPlaceholder>
  )
}

implementPropTypes(WorkspacesInput, DataInputTypes, {
  value: T.arrayOf(T.shape(WorkspaceType.propTypes)),
  picker: T.shape({
    title: T.string
  })
}, {
  value: null
})

export {
  WorkspacesInput
}
