import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import merge from 'lodash/merge'
import cloneDeep from 'lodash/cloneDeep'
import uniq from 'lodash/uniq'

// TODO : use custom components instead
import Tab from 'react-bootstrap/lib/Tab'
import Tabs from 'react-bootstrap/lib/Tabs'

import {param} from '#/main/app/config'
import {trans}  from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'
import {PopoverButton} from '#/main/app/buttons/popover/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {MODAL_ROLES_PICKER} from '#/main/core/modals/roles'
import {
  getSimpleAccessRule,
  setSimpleAccessRule,
  hasCustomRules,
  isStandardRole
} from '#/main/core/resource/permissions'

const CreatePermission = props => {
  const availableTypes = param('resources.types')

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
                checked={props.permission && 0 < props.permission.length}
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
                      checked={props.permission && -1 !== props.permission.indexOf(resourceType.name)}
                      onChange={(e) => {
                        if (e.target.checked) {
                          props.onChange([].concat(props.permission || [], [resourceType.name]))
                        } else {
                          const newPerm = props.permission ? props.permission.slice() : []
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
          'label-primary': props.permission && 0 < props.permission.length,
          'label-default': !props.permission || 0 === props.permission.length
        })}>
          {props.permission.length || '0'}
        </span>
      </PopoverButton>
    </td>
  )
}

CreatePermission.propTypes = {
  id: T.string.isRequired,
  permission: T.oneOfType([T.array, T.bool]).isRequired,
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
            onChange={() => props.update(merge({}, props.permissions, {[permission]: !props.permissions[permission]}))}
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

            props.update(newPerms)
          }}
        />
    )}

    <td className="delete-cell">
      {props.deletable &&
        <Button
          className="btn btn-link"
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-trash-o"
          label={trans('delete', {}, 'actions')}
          tooltip="left"
          callback={props.delete}
          dangerous={true}
        />
      }
    </td>
  </tr>

RolePermissions.propTypes = {
  name: T.string.isRequired,
  translationKey: T.string.isRequired,
  permissions: T.object.isRequired,
  deletable: T.bool,
  update: T.func.isRequired,
  delete: T.func.isRequired
}

const AdvancedTab = props => {
  const allPerms = uniq(props.permissions
    .reduce((accumulator, current) => accumulator.concat(
      Object.keys(current.permissions)
    ), []))

  const defaultPerms = allPerms.reduce((acc, perm) => Object.assign(acc, {
    [perm]: false
  }), {})

  const hasNonStandardPerms = true

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

          {hasNonStandardPerms &&
            <td scope="col"></td>
          }
        </tr>
      </thead>

      <tbody>
        {props.permissions.map((rolePerm, index) => {
          const workspaceCode = rolePerm.workspace ? rolePerm.workspace.code : null
          const displayName = workspaceCode ? rolePerm.translationKey + ' (' + workspaceCode + ')': rolePerm.translationKey
          return (<RolePermissions
            key={rolePerm.id}
            name={rolePerm.name}
            translationKey={displayName}
            permissions={Object.assign({}, defaultPerms, rolePerm.permissions)}
            deletable={!isStandardRole(rolePerm.name, props.workspace)}
            update={(permissions) => {
              const newPerms = cloneDeep(props.permissions)
              const rights = newPerms.find(perm => perm.name === rolePerm.name)
              rights.permissions = permissions

              props.updatePermissions(newPerms)
            }}
            delete={() => {
              const newPerms = cloneDeep(props.permissions)
              newPerms.splice(index, 1)

              props.updatePermissions(newPerms)
            }}
          />)
        }
        )}
      </tbody>

      <tfoot>
        <tr>
          <td colSpan={allPerms.length + (hasNonStandardPerms ? 2 : 1)}>
            <ModalButton
              className="btn btn-block"
              size="sm"
              modal={[MODAL_ROLES_PICKER, {
                selectAction: (selectedRoles) => ({
                  type: CALLBACK_BUTTON,
                  callback: () => props.updatePermissions([].concat(props.permissions, selectedRoles.map(role => ({
                    name: role.name,
                    translationKey: role.translationKey,
                    permissions: {}
                  }))))
                })
              }]}
            >
              <span className="fa fa-fw fa-plus icon-with-text-right" />
              {trans('add_rights')}
            </ModalButton>
          </td>
        </tr>
      </tfoot>
    </table>
  )
}

AdvancedTab.propTypes = {
  workspace: T.shape({
    id: T.string.isRequired
  }),
  permissions: T.arrayOf(T.shape({
    name: T.string.isRequired,
    translationKey: T.string.isRequired,
    permissions: T.object.isRequired
  })).isRequired,
  updatePermissions: T.func.isRequired
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
        toggleMode={(mode) => props.updateRights(
          setSimpleAccessRule(props.resourceNode.rights, mode, props.resourceNode.workspace)
        )}
      />
    </Tab>

    <Tab eventKey="advanced" title={trans('advanced')}>
      <AdvancedTab
        workspace={props.resourceNode.workspace}
        permissions={props.resourceNode.rights}
        updatePermissions={props.updateRights}
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
