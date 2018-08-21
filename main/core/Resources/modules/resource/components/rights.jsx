import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import merge from 'lodash/merge'
import cloneDeep from 'lodash/cloneDeep'
import uniq from 'lodash/uniq'

import Tab from 'react-bootstrap/lib/Tab'
import Tabs from 'react-bootstrap/lib/Tabs'

import {param} from '#/main/app/config'
import {trans}  from '#/main/core/translation'
import {PopoverButton} from '#/main/app/buttons/popover/components/button'

import {
  getSimpleAccessRule,
  setSimpleAccessRule,
  hasCustomRules
} from '#/main/core/resource/rights'

const CreatePermission = props => {
  const availableTypes = param('resourceTypes')

  return (
    <td
      key="create-cell"
      className="create-cell"
    >
      <PopoverButton
        id={`${props.id}-resources-creation`}
        className="btn btn-link"
        popover={{
          position: 'left',
          className: 'popover-list-group',
          label: (
            <label className="checkbox-inline">
              <input
                type="checkbox"
                checked={0 < props.permission.length}
                onChange={(e) => {
                  if (e.target.checked) {
                    props.onChange(availableTypes.map(type => type.name))
                  } else {
                    props.onChange([])
                  }
                }}
              />
              {trans('resource_type')}
            </label>
          ),
          content: (
            <ul className="list-group">
              {availableTypes.map(resourceType =>
                <li key={resourceType.name} className="list-group-item">
                  <label className="checkbox-inline">
                    <input
                      type="checkbox"
                      checked={-1 !== props.permission.indexOf(resourceType.name)}
                      onChange={(e) => {
                        if (e.target.checked) {
                          props.onChange([].concat(props.permission, [resourceType.name]))
                        } else {
                          const newPerm = props.permission.slice()
                          newPerm.splice(newPerm.indexOf(resourceType.name), 1)
                          props.onChange(newPerm)
                        }
                      }}
                    />
                    {trans(resourceType.name, {}, 'resource')}
                  </label>
                </li>
              )}
            </ul>
          )
        }}
      >
        <span className={classes('label', {
          'label-primary': 0 < props.permission.length,
          'label-default': 0 === props.permission.length
        })}>
          {props.permission.length}
        </span>
      </PopoverButton>
    </td>
  )
}

CreatePermission.propTypes = {
  id: T.string.isRequired,
  permission: T.array.isRequired,
  onChange: T.func.isRequired
}

const RolePermissions = props =>
  <tr>
    <th scope="row">
      {trans(props.translationKey)}
    </th>

    {Object.keys(props.permissions).map(permission =>
      'create' !== permission ?
        <td
          key={permission}
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
        <CreatePermission
          key={permission}
          id={props.name}
          permission={props.permissions[permission]}
          onChange={(creationPerms) => {
            const newPerms = merge({}, props.permissions)
            newPerms.create = creationPerms
            props.updatePermissions(newPerms)
          }}
        />
    )}
  </tr>

RolePermissions.propTypes = {
  name: T.string.isRequired,
  translationKey: T.string.isRequired,
  permissions: T.object.isRequired,
  updatePermissions: T.func.isRequired
}

const AdvancedTab = props => {
  const allPerms = uniq(props.permissions
    .reduce((accumulator, current) => accumulator.concat(
      Object.keys(current.permissions)
    ), []))

  return (
    <table className="table table-striped table-hover resource-rights-advanced">
      <thead>
        <tr>
          <th scope="col">{trans('role')}</th>
          {allPerms.map(permission =>
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
            name={rolePerm.name}
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
