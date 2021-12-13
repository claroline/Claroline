import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'

import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {Sections, Section} from '#/main/app/content/components/sections'
import {DetailsData} from '#/main/app/content/details/components/data'
import {ListData} from '#/main/app/content/list/containers/data'

import {User as UserTypes} from '#/main/core/user/prop-types'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {actions} from '#/main/core/administration/community/user/store'
import {selectors as baseSelectors} from '#/main/core/administration/community/store'
import {OrganizationList} from '#/main/core/administration/community/organization/components/organization-list'
import {GroupList} from '#/main/core/administration/community/group/components/group-list'
import {RoleList} from '#/main/core/administration/community/role/components/role-list'

// todo : maybe merge UserCompare with the content of about modal
// todo : fixes titles level
// todo : merge embedded list with the one from the form if possible

const UserCompare = props =>
  <div className="panel panel-default embedded-details-section">
    <div className="panel-heading text-center">
      <UserAvatar picture={props.user.picture} alt={false} />
      <h3 className="panel-title">{props.user.username}</h3>
    </div>

    <DetailsData
      data={props.user}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'firstName',
              label: trans('firstName'),
              type: 'string'
            }, {
              name: 'lastName',
              label: trans('lastName'),
              type: 'string'
            }, {
              name: 'email',
              label: trans('email'),
              type: 'email'
            }, {
              name: 'meta.created',
              label: trans('creation_date'),
              type: 'date',
              options: {
                time: true
              }
            }, {
              name: 'meta.lastLogin',
              label: trans('last_login'),
              type: 'date',
              options: {
                time: true
              }
            }, {
              name: 'meta.mailValidated',
              label: trans('email_validated'),
              type: 'boolean'
            }, {
              name: 'restrictions.disabled',
              label: trans('user_disabled'),
              type: 'boolean'
            }
          ]
        }
      ]}
    >
      <Sections
        level={3}
      >
        <Section
          className="embedded-list-section"
          icon="fa fa-fw fa-users"
          title={trans('groups')}
        >
          <ListData
            name={`${baseSelectors.STORE_NAME}.users.compare.groupsUser${props.index}`}
            fetch={{
              url: ['apiv2_user_list_groups', {id: props.user.id}],
              autoload: true
            }}
            definition={GroupList.definition}
            card={GroupList.card}
            selectable={false}
          />
        </Section>

        <Section
          className="embedded-list-section"
          icon="fa fa-fw fa-building"
          title={trans('organizations')}
        >
          <ListData
            name={`${baseSelectors.STORE_NAME}.users.compare.organizationsUser${props.index}`}
            fetch={{
              url: ['apiv2_user_list_organizations', {id: props.user.id}],
              autoload: true
            }}
            definition={OrganizationList.definition}
            card={OrganizationList.card}
            selectable={false}
          />
        </Section>

        <Section
          className="embedded-list-section"
          icon="fa fa-fw fa-id-badge"
          title={trans('roles')}
        >
          <ListData
            name={`${baseSelectors.STORE_NAME}.users.compare.rolesUser${props.index}`}
            fetch={{
              url: ['apiv2_user_list_roles', {id: props.user.id}],
              autoload: true
            }}
            definition={RoleList.definition}
            card={RoleList.card}
            selectable={false}
          />
        </Section>
      </Sections>
    </DetailsData>

    <Button
      className="panel-btn btn"
      type={CALLBACK_BUTTON}
      primary={true}
      label={trans('keep_user')}
      callback={props.merge}
      disabled={props.disabled}
      confirm={{
        title: trans('merge'),
        message: trans('merge_confirmation', {username: props.user.username}),
        button: trans('merge', {}, 'actions')
      }}
    />
  </div>

UserCompare.propTypes = {
  index: T.number.isRequired,
  disabled: T.bool.isRequired,
  user: T.shape({
    id: T.string.isRequired,
    picture: T.string,
    username: T.string
  }).isRequired,
  merge: T.func.isRequired
}

const UsersMergeForm = props => 0 !== props.selectedUsers.length ?
  <div className="row">
    <div className="col-md-6">
      <UserCompare
        index={0}
        user={props.selectedUsers[0]}
        merge={() => props.mergeUsers(props.selectedUsers[0], props.selectedUsers[1], props.history.push)}
        disabled={props.selectedUsers[1].id === props.currentUser.id}
      />
    </div>

    <div className="col-md-6">
      <UserCompare
        index={1}
        user={props.selectedUsers[1]}
        merge={() => props.mergeUsers(props.selectedUsers[1], props.selectedUsers[0], props.history.push)}
        disabled={props.selectedUsers[0].id === props.currentUser.id}
      />
    </div>
  </div> :
  <div>Loading</div>

UsersMergeForm.propTypes = {
  currentUser: T.shape({
    id: T.string.isRequired
  }),
  selectedUsers: T.arrayOf(T.shape(
    UserTypes.propTypes
  )),
  mergeUsers: T.func.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired
}

const UsersMerge = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    selectedUsers: baseSelectors.selected(state) // TODO : use a selector
  }),
  (dispatch) => ({
    mergeUsers(userToKeep, userToRemove, navigate) {
      dispatch(actions.merge(userToKeep.id, userToRemove.id, navigate))
    }
  })
)(UsersMergeForm)

export {
  UsersMerge
}
