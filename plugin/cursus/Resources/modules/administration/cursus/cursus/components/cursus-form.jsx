import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {FormData} from '#/main/app/content/form/containers/data'
import {ListData} from '#/main/app/content/list/containers/data'

import {trans} from '#/main/core/translation'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections'
import {OrganizationList} from '#/main/core/administration/user/organization/components/organization-list'

import {Cursus as CursusType} from '#/plugin/cursus/administration/cursus/prop-types'
import {actions} from '#/plugin/cursus/administration/cursus/cursus/store'

const CursusFormComponent = (props) =>
  <FormData
    level={3}
    name="cursus.current"
    buttons={true}
    target={(cursus, isNew) => isNew ?
      ['apiv2_cursus_create'] :
      ['apiv2_cursus_update', {id: cursus.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: '/cursus',
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
            label: trans('code')
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
            name: 'meta.workspace',
            type: 'string',
            label: trans('workspace')
          }, {
          //   name: 'meta.icon',
          //   type: 'file',
          //   label: trans('icon')
          // }, {
            name: 'meta.blocking',
            type: 'boolean',
            label: trans('blocking', {}, 'cursus'),
            required: true
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
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_organizations'),
            callback: () => props.pickOrganizations(props.cursus.id)
          }
        ]}
      >
        <ListData
          name="cursus.current.organizations.list"
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
  new: T.bool.isRequired,
  cursus: T.shape(CursusType.propTypes).isRequired,
  pickOrganizations: T.func.isRequired
}

const CursusForm = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, 'cursus.current')),
    cursus: formSelect.data(formSelect.form(state, 'cursus.current'))
  }),
  dispatch => ({
    pickOrganizations(cursusId) {
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-building',
        title: trans('add_organizations'),
        confirmText: trans('add'),
        name: 'cursus.current.organizations.picker',
        definition: OrganizationList.definition,
        card: OrganizationList.card,
        fetch: {
          url: ['apiv2_organization_list'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addOrganizations(cursusId, selected))
      }))
    }
  })
)(CursusFormComponent)

export {
  CursusForm
}
