import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {FormData} from '#/main/app/content/form/containers/data'
import {ListData} from '#/main/app/content/list/containers/data'

import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {OrganizationList} from '#/main/core/administration/community/organization/components/organization-list'

import {selectors} from '#/plugin/cursus/administration/cursus/store'
import {actions} from '#/plugin/cursus/administration/cursus/cursus/store'

const CursusFormComponent = (props) =>
  <FormData
    level={3}
    name={selectors.STORE_NAME + '.cursus.current'}
    buttons={true}
    target={(cursus, isNew) => isNew ?
      ['apiv2_cursus_create'] :
      ['apiv2_cursus_update', {id: cursus.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: `${props.path}/cursus`,
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'title',
            type: 'string',
            label: trans('title'),
            required: true
          }, {
            name: 'code',
            type: 'string',
            label: trans('code'),
            required: true
          }, {
            name: 'description',
            type: 'html',
            label: trans('description')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-cogs',
        title: trans('parameters'),
        fields: [
          {
            name: 'workspace',
            type: 'workspace',
            label: trans('workspace')
          }, {
            name: 'meta.blocking',
            type: 'boolean',
            label: trans('blocking', {}, 'cursus'),
            help: trans('blocking_desc', {}, 'cursus')
          }, {
            name: 'meta.color',
            type: 'color',
            label: trans('color')
          }
        ]
      }
    ]}
  >
    <FormSections level={3}>
      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-building"
        title={trans('organizations')}
        disabled={props.new}
        actions={[
          {
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_organizations'),
            modal: [MODAL_DATA_LIST, {
              icon: 'fa fa-fw fa-building',
              title: trans('add_organizations'),
              confirmText: trans('add'),
              name: selectors.STORE_NAME + '.cursus.current.organizations.picker',
              definition: OrganizationList.definition,
              card: OrganizationList.card,
              fetch: {
                url: ['apiv2_organization_list'],
                autoload: true
              },
              handleSelect: (selected) => props.addOrganizations(props.cursus.id, selected)
            }]
          }
        ]}
      >
        <ListData
          name={selectors.STORE_NAME + '.cursus.current.organizations.list'}
          fetch={{
            url: ['apiv2_cursus_list_organizations', {id: props.cursus.id}],
            autoload: props.cursus.id && !props.new
          }}
          primaryAction={OrganizationList.open}
          delete={{
            url: ['apiv2_cursus_remove_organizations', {id: props.cursus.id}]
          }}
          definition={OrganizationList.definition}
          card={OrganizationList.card}
        />
      </FormSection>
    </FormSections>
  </FormData>

CursusFormComponent.propTypes = {
  path: T.string.isRequired,
  new: T.bool.isRequired,
  cursus: T.shape({
    id: T.string
  }).isRequired,
  addOrganizations: T.func.isRequired,
  addUsers: T.func.isRequired,
  addGroups: T.func.isRequired
}

const CursusForm = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME + '.cursus.current')),
    cursus: formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.cursus.current'))
  }),
  dispatch => ({
    addOrganizations(cursusId, organizationIds) {
      dispatch(actions.addOrganizations(cursusId, organizationIds))
    },
    addUsers(cursusId, users) {
      dispatch(actions.addUsers(cursusId, users))
    },
    addGroups(cursusId, groups) {
      dispatch(actions.addGroups(cursusId, groups))
    }
  })
)(CursusFormComponent)

export {
  CursusForm
}
