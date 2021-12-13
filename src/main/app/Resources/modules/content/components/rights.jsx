import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'
import uniq from 'lodash/uniq'

import {trans}  from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'
import {PopoverButton} from '#/main/app/buttons/popover/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {constants} from '#/main/core/user/constants'
import {MODAL_ROLES} from '#/main/core/modals/roles'
import {isStandardRole, hasCustomRoles, roleWorkspace} from '#/main/core/user/permissions'

const CreatePermission = props =>
  <td key="create-cell" className="create-cell">
    <PopoverButton
      id={`${props.id}-rights-creation`}
      className="btn btn-link"
      popover={{
        position: 'left',
        className: 'popover-list-group',
        label: (
          <label className="checkbox-inline">
            <input
              type="checkbox"
              disabled={!props.editable}
              checked={props.permission && 0 < props.permission.length}
              onChange={(e) => {
                if (e.target.checked) {
                  props.onChange(Object.keys(props.creatable))
                } else {
                  props.onChange([])
                }
              }}
            />
            {trans('all')}
          </label>
        ),
        content: (
          <ul className="list-group">
            {Object.keys(props.creatable).map(type =>
              <li key={type} className="list-group-item">
                <label className="checkbox-inline">
                  <input
                    type="checkbox"
                    disabled={!props.editable}
                    checked={props.permission && -1 !== props.permission.indexOf(type)}
                    onChange={(e) => {
                      if (e.target.checked) {
                        props.onChange([].concat(props.permission || [], [type]))
                      } else {
                        const newPerm = props.permission ? props.permission.slice() : []
                        newPerm.splice(newPerm.indexOf(type), 1)
                        props.onChange(newPerm)
                      }
                    }}
                  />
                  {props.creatable[type]}
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

CreatePermission.propTypes = {
  id: T.string.isRequired,
  creatable: T.object.isRequired,
  editable: T.bool.isRequired,
  permission: T.oneOfType([T.array, T.bool]).isRequired,
  onChange: T.func.isRequired
}

const RolePermissions = props =>
  <tr>
    <th scope="row">
      {props.translationKey}
    </th>

    {Object.keys(props.permissions).map(permission =>
      ('create' !== permission || isEmpty(props.creatable)) ?
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
            disabled={!props.editable}
            onChange={() => props.update(merge({}, props.permissions, {[permission]: !props.permissions[permission]}))}
          />
        </td>
        :
        <CreatePermission
          key={permission}
          id={props.name}
          permission={props.permissions[permission]}
          editable={props.editable}
          creatable={props.creatable}
          onChange={(creationPerms) => {
            const newPerms = merge({}, props.permissions)
            newPerms.create = creationPerms

            props.update(newPerms)
          }}
        />
    )}

    {props.hasNonStandardPerms &&
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
    }
  </tr>

RolePermissions.propTypes = {
  name: T.string.isRequired,
  translationKey: T.string.isRequired,
  permissions: T.object.isRequired,
  hasNonStandardPerms: T.bool,
  deletable: T.bool,
  editable: T.bool,
  creatable: T.object,
  update: T.func.isRequired,
  delete: T.func.isRequired
}

const ContentRights = props => {
  const allPerms = uniq(props.rights
    .reduce((accumulator, current) => accumulator.concat(
      Object.keys(current.permissions)
    ), []))

  const defaultPerms = allPerms.reduce((acc, perm) => Object.assign(acc, {
    [perm]: false
  }), {})

  const hasNonStandardPerms = hasCustomRoles(props.rights, props.workspace)

  return (
    <table className="table table-striped table-hover content-rights-advanced">
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
            <td scope="col" />
          }
        </tr>
      </thead>

      <tbody>
        {[]
          // create new array to be able to modify it
          .concat(props.rights)
          // move workspace manager role to the top of the list
          .sort((a, b) => props.workspace && roleWorkspace(props.workspace, true) === b.name ? 1 : 0)
          .map((rolePerm) => {
            const workspaceCode = rolePerm.workspace ? rolePerm.workspace.code : null
            const displayName = trans(rolePerm.translationKey) + (workspaceCode ? ' (' + workspaceCode + ')' : '')
            let managerRole = null
            if (props.workspace) {
              managerRole = roleWorkspace(props.workspace, true)
            }

            return (
              <RolePermissions
                key={rolePerm.id || rolePerm.name}
                name={rolePerm.name}
                translationKey={displayName}
                permissions={Object.assign({}, defaultPerms, rolePerm.permissions)}
                deletable={!isStandardRole(rolePerm.name, props.workspace)}
                creatable={props.creatable}
                editable={!managerRole || rolePerm.name !== managerRole}
                hasNonStandardPerms={hasNonStandardPerms}
                update={(permissions) => {
                  const newPerms = merge([], props.rights)
                  const rights = newPerms.find(perm => perm.name === rolePerm.name)
                  rights.permissions = permissions

                  props.updateRights(newPerms)
                }}
                delete={() => {
                  const newPerms = merge([], props.rights)
                  // rights have been reordered for display, we need to retrieve perm position in stored data
                  const realIndex = newPerms.findIndex(p => p.name === rolePerm.name)
                  if (-1 !== realIndex) {
                    newPerms.splice(realIndex, 1)

                    props.updateRights(newPerms)
                  }
                }}
              />
            )
          })
        }
      </tbody>

      <tfoot>
        <tr>
          <td colSpan={allPerms.length + (hasNonStandardPerms ? 2 : 1)}>
            <ModalButton
              className="btn btn-block"
              size="sm"
              modal={[MODAL_ROLES, {
                filters: [
                  {property: 'type', value: constants.ROLE_PLATFORM}
                ],
                selectAction: (selectedRoles) => ({
                  type: CALLBACK_BUTTON,
                  callback: () => props.updateRights([].concat(props.rights, selectedRoles.map(role => ({
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

ContentRights.propTypes = {
  creatable: T.object,
  workspace: T.shape({

  }),
  rights: T.arrayOf(T.shape({
    name: T.string.isRequired,
    translationKey: T.string.isRequired,
    permissions: T.object.isRequired
  })).isRequired,
  updateRights: T.func.isRequired
}

export {
  ContentRights
}
