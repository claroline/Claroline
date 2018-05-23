import React from 'react'

import {connect} from 'react-redux'
import filter from 'lodash/filter'
import get from 'lodash/get'
import {constants as listConst} from '#/main/core/data/list/constants'

import {t} from '#/main/core/translation'
import {actions} from '#/main/core/administration/user/user/actions'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_CONFIRM} from '#/main/core/layout/modal'
import {currentUser} from '#/main/core/user/current'

import {ComparisonTable} from '#/main/core/data/comparisonTable/components/comparison-table.jsx'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {RoleList} from '#/main/core/administration/user/role/components/role-list.jsx'



const RolesAndWsList = props =>
  <DataListContainer
    name={`users.compare.rolesUser${props.index}`}
    open={RoleList.action}
    fetch={{
      url: ['apiv2_user_list_roles', {id: props.id}],
      autoload: true
    }}
    definition={RoleList.definition}
    card={RoleList.card}
    filterColumns={false}
    display={{
      available: [listConst.DISPLAY_TABLE_SM],
      current: listConst.DISPLAY_TABLE_SM
    }}
    selection={null}
    filter={null}
  />
RolesAndWsList.displayName = 'RolesAndWsList'

const UsersMergeForm = props =>
  <ComparisonTable
    data={props.data}
    rows={[
      {
        name: 'firstName',
        label: t('firstName'),
        type: 'string'
      },
      {
        name: 'lastName',
        label: t('lastName'),
        type: 'string'
      },
      {
        name: 'username',
        label: t('username'),
        type: 'username'
      },
      {
        name: 'email',
        label: t('email'),
        type: 'email'
      },
      {
        name: 'meta.created',
        label: t('creation_date'),
        type: 'date',
        options: {
          time: true
        }
      },
      {
        name: 'meta.lastLogin',
        label: t('last_login'),
        type: 'date',
        options: {
          time: true
        }
      },
      {
        name: 'cas_data.id',
        label: t('cas_id'),
        type: 'string'
      },
      {
        name: 'meta.mailValidated',
        label: t('email_validated'),
        type: 'boolean'
      },
      {
        name: 'restrictions.disabled',
        label: t('user_disabled'),
        type: 'boolean'
      },
      {
        name: 'roles',
        label: t('roles_and_workspaces'),
        renderer: (data, index, id) => {
          const Comp = <RolesAndWsList
            data={data}
            index={index}
            id={id}
          />

          return Comp
        }
      }
    ]}
    action={{
      text: () => t('keep_user'),
      action: (selected, data) => props.mergeModal(selected, data),
      disabled: (selected, data) => {
        const me = currentUser()
        // There is no need to check if me.id exists. We're in a restricted area, a user must be logged in
        return getOtherUserPropValue(selected, data, 'id') === me.id
      }
    }}
    title={(selected) => t('username') + ' : ' + selected.username}
  />

const UsersMerge = connect(
  state => ({
    data: state.users.compare.selected
  }),
  dispatch => ({
    mergeModal(selected, data) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          confirmButtonText: t('merge'),
          dangerous: true,
          question: t('merge_confirmation', {'username': selected.username}),
          handleConfirm: () => {
            dispatch(actions.merge(selected.id, getOtherUserPropValue(selected, data, 'id')))
          }
        })
      )
    }
  })
)(UsersMergeForm)

export {
  UsersMerge
}

function getOtherUserPropValue(selected, data, propName) {
  let others = filter(data, (o) => o.id !== selected.id)
  return get(others[0], propName)
}