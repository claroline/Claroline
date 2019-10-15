import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {Button} from '#/main/app/action/components/button'

import {MODAL_RESOURCES} from '#/main/core/modals/resources'
import {
  Requirements as RequirementsType,
  Workspace as WorkspaceType
} from '#/main/core/workspace/prop-types'

const ResourceRow = props =>
  <div className="tool-rights-row list-group-item">
    <div>
      {`${props.name} [${trans(props.type, {}, 'resource')}]`}
    </div>
    <div>
      <Button
        className="btn btn-link"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-trash-o"
        label={trans('delete', {}, 'actions')}
        dangerous={true}
        callback={() => props.removeResource()}
        tooltip="left"
      />
    </div>
  </div>

ResourceRow.propTypes = {
  name: T.string.isRequired,
  type: T.string.isRequired,
  removeResource: T.func.isRequired
}

const RequirementsDetails = (props) => props.requirements ?
  <div style={{marginTop: 20}}>
    {props.requirements.role &&
      <div>
        <b>{trans('role')}</b> : {trans(props.requirements.role.translationKey)}
      </div>
    }
    {props.requirements.user &&
      <div>
        <b>{trans('user')}</b> : {`${props.requirements.user.lastName} ${props.requirements.user.firstName}`}
      </div>
    }
    <FormSections
      level={3}
      defaultOpened="resources-section"
    >
      <FormSection
        id="resources-section"
        key="resources-section"
        icon="fa fa-fw fa-folder"
        title={trans('resources')}
        expanded={true}
        actions={[
          {
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_resources'),
            modal: [MODAL_RESOURCES, {
              title: trans('add_resources'),
              current: props.root,
              root: props.root,
              filters: [{property: 'workspace', value: props.workspace.uuid, locked: true}],
              selectAction: (selectedResources) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addResources(props.requirements, selectedResources)
              })
            }]
          }
        ]}
        style={{marginTop: 20}}
      >
        {props.requirements.resources && 0 < props.requirements.resources.length ?
          <div className="list-group" fill={true}>
            {props.requirements.resources.map(resource =>
              <ResourceRow
                key={`resource-row-${resource.id}`}
                name={resource.name}
                type={resource.meta.type}
                removeResource={() => props.removeResources(props.requirements, [resource])}
              />
            )}
          </div> :
          <div className="alert alert-warning">
            {trans('no_resource')}
          </div>
        }
      </FormSection>
    </FormSections>
  </div> :
  <div>
  </div>

RequirementsDetails.propTypes = {
  workspace: T.shape(WorkspaceType.propTypes).isRequired,
  root: T.object.isRequired,
  requirements: T.shape(RequirementsType.propTypes).isRequired,
  addResources: T.func.isRequired,
  removeResources: T.func.isRequired
}

export {
  RequirementsDetails
}
