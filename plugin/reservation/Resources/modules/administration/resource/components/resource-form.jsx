import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DATA_PICKER} from '#/main/core/data/list/modals'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections.jsx'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {OrganizationList} from '#/main/core/administration/user/organization/components/organization-list.jsx'

import {actions} from '#/plugin/reservation/administration/resource/actions'

const ResourceRigths = props =>
  <ul className="list-group">
    {props.resourceRights.map(rr =>
      <li
        key={`permissions-${rr.id}`}
        className="list-group-item resource-rights-item"
      >
        {trans(rr.role.translationKey, {}, 'platform')}
        <span className="btn-group">
          <button
            type="button"
            className={`btn ${rr.mask === 0 ? 'btn-primary' : 'btn-default'}`}
            onClick={() => props.onChange(rr, 0)}
          >
            {trans('agenda.resource.cannot_see', {}, 'reservation')}
          </button>
          <button
            type="button"
            className={`btn ${rr.mask === 1 ? 'btn-primary' : 'btn-default'}`}
            onClick={() => props.onChange(rr, 1)}
          >
            {trans('agenda.resource.see', {}, 'reservation')}
          </button>
          <button
            type="button"
            className={`btn ${rr.mask === 3 ? 'btn-primary' : 'btn-default'}`}
            onClick={() => props.onChange(rr, 3)}
          >
            {trans('agenda.resource.book', {}, 'reservation')}
          </button>
          <button
            type="button"
            className={`btn ${rr.mask === 7 ? 'btn-primary' : 'btn-default'}`}
            onClick={() => props.onChange(rr, 7)}
          >
            {trans('agenda.resource.admin', {}, 'reservation')}
          </button>
        </span>
      </li>
    )}
  </ul>

ResourceRigths.propTypes = {
  resourceRights: T.arrayOf(T.shape({
    id: T.string.isRequired,
    mask: T.number.isRequired,
    role: T.shape({
      translationKey: T.string.isRequired
    }).isRequired
  })).isRequired,
  onChange: T.func.isRequired
}

const Resource = props => {
  const choices = {}
  props.resourceTypes.reduce((o, rt) => Object.assign(o, {[rt.name]: rt.name}), choices)

  return (
    <FormContainer
      level={2}
      name="resourceForm"
      sections={[
        {
          id: 'general',
          title: trans('general', {}, 'platform'),
          primary: true,
          fields: [
            {
              name: 'name',
              type: 'string',
              label: trans('name', {}, 'platform'),
              required: true
            }, {
              name: 'resourceType.name',
              type: 'enum',
              label: trans('type', {}, 'platform'),
              required: true,
              options: {
                choices: choices
              }
            }, {
              name: 'description',
              type: 'html',
              label: trans('description', {}, 'platform')
            }, {
              name: 'localization',
              type: 'string',
              label: trans('location', {}, 'platform')
            }, {
              name: 'quantity',
              type: 'number',
              label: trans('quantity', {}, 'reservation'),
              required: true,
              options: {
                min: 1
              }
            }, {
              name: 'color',
              type: 'color-picker',
              label: trans('color', {}, 'platform')
            }
          ]
        }
      ]}
    >
      <FormSections level={3}>
        <FormSection
          id="resource-organizations"
          icon="fa fa-fw fa-building"
          title={trans('organizations', {}, 'platform')}
          disabled={props.new}
          actions={[
            {
              icon: 'fa fa-fw fa-plus',
              label: trans('add_organizations', {}, 'platform'),
              action: () => props.pickOrganizations(props.resource.id)
            }
          ]}
        >
          <DataListContainer
            name="resourceForm.organizations"
            open={OrganizationList.open}
            fetch={{
              url: ['apiv2_reservationresource_list_organizations', {id: props.resource.id}],
              autoload: props.resource.id && !props.new
            }}
            delete={{
              url: ['apiv2_reservationresource_remove_organizations', {id: props.resource.id}]
            }}
            definition={OrganizationList.definition}
            card={OrganizationList.card}
          />
        </FormSection>
        <FormSection
          id="resource-rights"
          icon="fa fa-fw fa-unlock"
          title={trans('permissions', {}, 'platform')}
          disabled={props.new}
          actions={[
            {
              icon: 'fa fa-fw fa-plus',
              label: trans('add_roles', {}, 'platform'),
              action: () => props.pickRoles(props.resource.id, props.resource.resourceRights ? props.resource.resourceRights : [])
            }
          ]}
        >
          {props.resource.resourceRights && props.resource.resourceRights.length > 0 ?
            <ResourceRigths
              resourceRights={props.resource.resourceRights ? props.resource.resourceRights : []}
              onChange={props.editResourceRights}
            /> :
            <div className="alert alert-warning">
              {trans('no_rights_configured', {}, 'reservation')}
            </div>
          }
          <div>
            {`${trans('agenda.resource.cannot_see', {}, 'reservation')}: ${trans('agenda.resource.cannot_see_info', {}, 'reservation')}`}
          </div>
          <div>
            {`${trans('agenda.resource.see', {}, 'reservation')}: ${trans('agenda.resource.see_info', {}, 'reservation')}`}
          </div>
          <div>
            {`${trans('agenda.resource.book', {}, 'reservation')}: ${trans('agenda.resource.book_info', {}, 'reservation')}`}
          </div>
          <div>
            {`${trans('agenda.resource.admin', {}, 'reservation')}: ${trans('agenda.resource.admin_info', {}, 'reservation')}`}
          </div>
          <div>
            <b>{trans('agenda.resource.rights_info', {}, 'reservation')}</b>
          </div>
        </FormSection>
      </FormSections>
    </FormContainer>
  )
}

Resource.propTypes = {
  new: T.bool.isRequired,
  resourceTypes: T.arrayOf(T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired
  })),
  resource: T.shape({
    id: T.string,
    resourceRights: T.array
  }).isRequired,
  editResourceRights: T.func.isRequired,
  pickOrganizations: T.func.isRequired,
  pickRoles: T.func.isRequired
}

const ResourceForm = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, 'resourceForm')),
    resource: formSelect.data(formSelect.form(state, 'resourceForm')),
    resourceTypes: state.resourceTypes
  }),
  dispatch =>({
    editResourceRights(rights, value) {
      dispatch(actions.editResourceRights(rights, value))
    },
    pickOrganizations(resourceId) {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-building',
        title: trans('add_organizations', {}, 'platform'),
        confirmText: trans('add', {}, 'platform'),
        name: 'organizationsPicker',
        definition: OrganizationList.definition,
        card: OrganizationList.card,
        fetch: {
          url: ['apiv2_organization_list'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addOrganizations(resourceId, selected))
      }))
    },
    pickRoles(resourceId, resourceRights) {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-id-badge',
        title: trans('add_roles', {}, 'platform'),
        confirmText: trans('add', {}, 'platform'),
        name: 'rolesPicker',
        definition: [{
          name: 'translationKey',
          type: 'translation',
          label: trans('name', {}, 'platform'),
          displayed: true
        }],
        card: (row) => ({
          onClick: () => {},
          poster: null,
          icon: 'fa fa-id-badge',
          title: trans(row.translationKey, {}, 'platform'),
          subtitle: ''
        }),
        fetch: {
          url: ['apiv2_platform_roles_list'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addRoles(resourceId, resourceRights, selected))
      }))
    }
  })
)(Resource)

export {
  ResourceForm
}
