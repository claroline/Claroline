import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {asset} from '#/main/core/scaffolding/asset'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'
import {select as formSelect} from '#/main/core/data/form/selectors'

import {PageActions} from '#/main/core/layout/page/components/page-actions.jsx'
import {FormSection} from '#/main/core/layout/form/components/form-sections.jsx'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DATA_PICKER} from '#/main/core/data/list/modals'
import {constants as listConst} from '#/main/core/data/list/constants'

import {actions} from '#/main/core/workspace/parameters/actions'

const ResourceSection = props =>
  <div>
    {!props.resource ?
      <div>
        {trans('no_resource')}
      </div> :
      <div>
        {props.resource.name} [{trans(props.resource.meta.type, {}, 'resource')}]
      </div>
    }
    <button
      type="button"
      className="btn btn-primary"
      onClick={() => props.pickResource()}
    >
      <span className="fa fa-fw fa-plus icon-with-text-right"/>
      {trans('add_resource')}
    </button>
  </div>

ResourceSection.propTypes = {
  resource: T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired,
    meta: T.shape({
      type: T.string.isRequired
    }).isRequired
  }),
  pickResource: T.func.isRequired,
  removeResource: T.func.isRequired
}

const Actions = () =>
  <PageActions>
    <FormPageActionsContainer
      formName="parameters"
      target={(workspace) => ['apiv2_workspace_update', {id: workspace.id}]}
      opened={true}
      cancel={{}}
    />
  </PageActions>

const Tab = (props) => {
  return (
    <div>
      <FormContainer
        level={3}
        name="parameters"
        sections={[
          {
            id: 'display',
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'options.hide_tools_menu',
                type: 'boolean',
                label: trans('hide_tools_menu'),
                required: false
              },
              {
                name: 'options.background_color',
                type: 'color',
                label: trans('background_color'),
                required: false
              },
              {
                name: 'options.hide_breadcrumb',
                type: 'boolean',
                label: trans('hide_breadcrumb'),
                required: false
              },
              {
                name: 'options.use_workspace_opening_resource',
                type: 'boolean',
                label: trans('open_resource_when_opening_ws'),
                required: false
              }
            ]
          }
        ]}
      >
        <FormSection
          id="resource-to-open"
          icon="fa fa-fw fa-folder-open-o"
          title={trans('resource_to_open')}
        >
          <ResourceSection
            resource={props.workspace.options.opened_resource}
            pickResource={props.pickResource}
            removeResource={props.removeResource}
          />
        </FormSection>
      </FormContainer>
    </div>
  )
}

Tab.propTypes = {
  workspace: T.shape({
    options: T.shape({
      opened_resource: T.bool.isRequired
    })
  }).isRequired,
  pickResource: T.func.isRequired,
  removeResource: T.func.isRequired
}

const ConnectedTab = connect(
  state => ({
    workspace: formSelect.data(formSelect.form(state, 'parameters'))
  }),
  dispatch => ({
    pickResource() {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-folder-open',
        title: trans('resource_to_open'),
        confirmText: trans('objects_select_confirm'),
        name: 'resourcesPicker',
        onlyId: false,
        display: {
          current: listConst.DISPLAY_TILES_SM,
          available: Object.keys(listConst.DISPLAY_MODES)
        },
        definition: [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            displayed: true,
            primary: true
          },
          {
            name: 'meta.type',
            type: 'string',
            label: trans('type'),
            displayed: true,
            filterable: false,
            renderer: (rowData) => trans(rowData.meta.type, {}, 'resource')
          },
          {
            name: 'workspace.name',
            type: 'string',
            label: trans('workspace'),
            displayed: true
          },
          {
            name: 'meta.parent.name',
            type: 'string',
            label: trans('parent'),
            displayed: true
          }
        ],
        card: (row) => ({
          poster: asset(row.meta.icon),
          icon: 'fa fa-folder-open',
          title: row.name,
          subtitle: trans(row.meta.type, {}, 'resource'),
          footer:
            <b>{row.workspace.name}</b>
        }),
        fetch: {
          url: ['apiv2_resources_picker'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.updateResource(selected[0]))
      }))
    }
  })
)(Tab)

export {
  ConnectedTab as DisplayTab,
  Actions as DisplayActions
}
