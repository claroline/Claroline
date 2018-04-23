import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'

import {FormContainer} from '#/main/core/data/form/containers/form.jsx'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections.jsx'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {actions as formActions} from '#/main/core/data/form/actions'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DATA_PICKER} from '#/main/core/data/list/modals'

import {Role as RoleTypes} from '#/main/core/user/prop-types'
import {actions} from '#/main/core/administration/user/role/actions'
import {GroupList} from '#/main/core/administration/user/group/components/group-list.jsx'
import {UserList} from '#/main/core/administration/user/user/components/user-list.jsx'

const RoleForm = props =>
  <FormContainer
    level={3}
    name="roles.current"
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'translationKey',
            type: 'translation',
            label: trans('name'),
            required: true,
            disabled: props.role.meta && props.role.meta.readOnly
          }
        ]
      }
    ]}
  >
    <FormSections level={3}>
      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-user"
        title={trans('users')}
        disabled={props.new}
        actions={[
          {
            type: 'callback',
            icon: 'fa fa-fw fa-plus',
            label: trans('add_user'),
            callback: () => props.pickUsers(props.role.id),
            disabled: props.role.restrictions && null !== props.role.restrictions.maxUsers && props.role.restrictions.maxUsers <= props.role.meta.users
          }
        ]}
      >
        <DataListContainer
          name="roles.current.users"
          fetch={{
            url: ['apiv2_role_list_users', {id: props.role.id}],
            autoload: props.role.id && !props.new
          }}
          primaryAction={UserList.open}
          delete={{
            url: ['apiv2_role_remove_users', {id: props.role.id}]
          }}
          definition={UserList.definition}
          card={UserList.card}
        />
      </FormSection>

      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-id-badge"
        title={trans('groups')}
        disabled={props.new}
        actions={[
          {
            type: 'callback',
            icon: 'fa fa-fw fa-plus',
            label: trans('add_group'),
            callback: () => props.pickGroups(props.role.id)
          }
        ]}
      >
        <DataListContainer
          name="roles.current.groups"
          primaryAction={GroupList.open}
          fetch={{
            url: ['apiv2_role_list_groups', {id: props.role.id}],
            autoload: props.role.id && !props.new
          }}
          delete={{
            url: ['apiv2_role_remove_groups', {id: props.role.id}]
          }}
          definition={GroupList.definition}
          card={GroupList.card}
        />
      </FormSection>
    </FormSections>
  </FormContainer>
RoleForm.propTypes = {
  new: T.bool.isRequired,
  role: T.shape(
    RoleTypes.propTypes
  ).isRequired,
  updateProp: T.func.isRequired,
  pickUsers: T.func.isRequired,
  pickGroups: T.func.isRequired
}

RoleForm.propTypes = {
  new: T.bool.isRequired,
  role: T.shape(
    RoleTypes.propTypes
  ).isRequired,
  updateProp: T.func.isRequired,
  pickUsers: T.func.isRequired,
  pickGroups: T.func.isRequired
}

const Role = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, 'roles.current')),
    role: formSelect.data(formSelect.form(state, 'roles.current'))
  }),
  dispatch => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp('roles.current', propName, propValue))
    },
    pickUsers(roleId) {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-user',
        title: trans('add_users'),
        confirmText: trans('add'),
        name: 'users.picker',
        definition: UserList.definition,
        card: UserList.card,
        fetch: {
          url: ['apiv2_user_list_managed'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addUsers(roleId, selected))
      }))
    },
    pickGroups(roleId){
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-users',
        title: trans('add_groups'),
        confirmText: trans('add'),
        name: 'groups.picker',
        definition: GroupList.definition,
        card: GroupList.card,
        fetch: {
          url: ['apiv2_group_list_managed'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addGroups(roleId, selected))
      }))
    }
  })
)(RoleForm)

export {
  Role
}
