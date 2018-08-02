import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import cloneDeep from 'lodash/cloneDeep'
import uniq from 'lodash/uniq'

import Tab from 'react-bootstrap/lib/Tab'
import Tabs from 'react-bootstrap/lib/Tabs'

import {trans}  from '#/main/core/translation'
import {PopoverButton} from '#/main/app/buttons/popover/components/button'

import {
  getSimpleAccessRule,
  setSimpleAccessRule,
  hasCustomRules
} from '#/main/core/resource/rights'

const CreatePermission = props =>
  <td
    key="create-cell"
    className="create-cell"
  >
    <PopoverButton
      className="btn btn-link"
      popover={{
        position: 'left',
        className: 'popover-list-group',
        label: (
          <label className="checkbox-inline">
            <input type="checkbox" />
            {trans('resource_type')}
          </label>
        ),
        content: (
          <ul className="list-group">
            {Object.keys(props.permission).map(resourceType =>
              <li key={resourceType} className="list-group-item">
                <label className="checkbox-inline">
                  <input
                    type="checkbox"
                    checked={props.permission[resourceType]}
                  />
                  {trans(resourceType, {}, 'resource')}
                </label>
              </li>
            )}
          </ul>
        )
      }}
    >
      <span className="fa fa-fw fa-folder-open" />
    </PopoverButton>
  </td>

CreatePermission.propTypes = {
  permission: T.object.isRequired
}

const RolePermissions = props =>
  <tr>
    <th scope="row">
      {trans(props.translationKey)}
    </th>

    {Object.keys(props.permissions).map(permission =>
      'create' !== permission ?
        <td
          key={`${permission}-checkbox`}
          className={classes({
            'checkbox-cell': 'create' !== permission,
            'create-cell': 'create' === permission
          })}
        >
          <input
            type="checkbox"
            checked={props.permissions[permission]}
            onChange={() => props.updatePermissions(merge({}, props.permissions, {[permission]: !props.permissions[permission]}))}
          />
        </td>
        :
        !isEmpty(props.permissions[permission]) && <CreatePermission permission={props.permissions[permission]} />
    )}
  </tr>

RolePermissions.propTypes = {
  translationKey: T.string.isRequired,
  permissions: T.object.isRequired,
  updatePermissions: T.func.isRequired
}

const AdvancedTab = props => {
  const allPerms = uniq(props.permissions
    .reduce((accumulator, current) => accumulator.concat(
      Object.keys(current.permissions).filter(perm => 'create' !== perm || !isEmpty(current.permissions[perm]))
    ), []))

  return (
    <table className="table table-striped table-hover resource-rights-advanced">
      <thead>
        <tr>
          <th scope="col">{trans('role')}</th>
          {allPerms.map(permission =>
            ('create' !== permission || !isEmpty(props.permissions['ROLE_USER'].permissions[permission])) &&
            <th key={`${permission}-header`} scope="col">
              <div className="permission-name-container">
                <span className="permission-name">{trans(permission, {}, 'actions')}</span>
              </div>
            </th>
          )}
        </tr>
      </thead>

      <tbody>
        {props.permissions.map(rolePerm =>
          <RolePermissions
            key={rolePerm.name}
            translationKey={rolePerm.translationKey}
            permissions={rolePerm.permissions}
            updatePermissions={(permissions) => props.updateRolePermissions(rolePerm.name, permissions)}
          />
        )}
      </tbody>
    </table>
  )
}

AdvancedTab.propTypes = {
  permissions: T.arrayOf(T.shape({
    name: T.string.isRequired,
    translationKey: T.string.isRequired,
    permissions: T.object.isRequired
  })).isRequired,
  updateRolePermissions: T.func.isRequired
}

const SimpleAccessRule = props =>
  <button
    className={classes('simple-right btn', {
      selected: props.mode === props.currentMode
    })}
    onClick={() => props.toggleMode(props.mode)}
  >
    <span className={classes('simple-right-icon', props.icon)} />
    <span className="simple-right-label">{trans('resource_rights_'+props.mode, {}, 'resource')}</span>
  </button>

SimpleAccessRule.propTypes = {
  icon: T.string.isRequired,
  mode: T.string.isRequired,
  currentMode: T.string.isRequired,
  toggleMode: T.func.isRequired
}

const SimpleTab = props =>
  <div className="resource-rights-simple">
    <p>{trans('resource_access_rights', {}, 'resource')}</p>

    <div className="resource-rights-simple-group">
      <SimpleAccessRule mode="all" icon="fa fa-globe" {...props} />
      <SimpleAccessRule mode="user" icon="fa fa-users" {...props} />
      <SimpleAccessRule mode="workspace" icon="fa fa-book" {...props} />
      <SimpleAccessRule mode="admin" icon="fa fa-lock" {...props} />
    </div>

    {props.customRules &&
      <p className="resource-custom-rules-info">
        <span className="fa fa-asterisk" />
        {trans('resource_rights_custom_help', {}, 'resource')}
      </p>
    }
  </div>

SimpleTab.propTypes = {
  currentMode: T.string,
  customRules: T.bool,
  toggleMode: T.func.isRequired
}

SimpleTab.defaultProps = {
  currentMode: '',
  customRules: false
}

const ResourceRights = props =>
  <Tabs id={`${props.resourceNode.id}-tabs`} defaultActiveKey="simple">
    <Tab eventKey="simple" title={trans('simple')}>
      <SimpleTab
        currentMode={getSimpleAccessRule(props.resourceNode.rights, props.resourceNode.workspace)}
        customRules={hasCustomRules(props.resourceNode.rights, props.resourceNode.workspace)}
        toggleMode={(mode) => {
          props.updateRights(
            setSimpleAccessRule(props.resourceNode.rights, mode, props.resourceNode.workspace)
          )
        }}
      />
    </Tab>

    <Tab eventKey="advanced" title={trans('advanced')}>
      <AdvancedTab
        permissions={props.resourceNode.rights}
        updateRolePermissions={(roleName, permissions) => {

          const newPerms = cloneDeep(props.resourceNode.rights)
          const rights = newPerms.find(perm => perm.name === roleName)
          rights.permissions = permissions

          props.updateRights(newPerms)
        }}
      />
    </Tab>
  </Tabs>

ResourceRights.propTypes = {
  resourceNode: T.shape({
    id: T.string.isRequired, // will not properly work in creation
    workspace: T.shape({
      id: T.string.isRequired
    }),
    rights: T.arrayOf(T.shape({
      name: T.string.isRequired,
      translationKey: T.string.isRequired,
      permissions: T.object.isRequired
    })).isRequired
  }).isRequired,
  updateRights: T.func.isRequired
}

export {
  ResourceRights
}
