import React, {forwardRef, Fragment, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {Collapse} from 'react-bootstrap'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'
import merge from 'lodash/merge'
import uniq from 'lodash/uniq'

import {trans}  from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MenuButton, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_ROLES} from '#/main/community/modals/roles'
import {roleWorkspace} from '#/main/community/permissions'

import {Menu, MenuHeader} from '#/main/app/overlays/menu'
import {Checkbox} from '#/main/app/input/components/checkbox'
import {DataMicro} from '#/main/app/data/components/micro'
import {Badge} from '#/main/app/components/badge'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'

const CreateMenu = forwardRef((props, ref) =>
  <ul
    {...omit(props, 'editable', 'permission', 'creatable', 'onChange', 'show', 'close', 'id')}
    ref={ref}
  >
    <MenuHeader>
      <Checkbox
        id={`${props.id}-all`}
        className="mb-0"
        switch={true}
        label={trans('all')}
        disabled={!props.editable}
        checked={props.permission && 0 < props.permission.length}
        onChange={(checked) => {
          if (checked) {
            props.onChange(Object.keys(props.creatable))
          } else {
            props.onChange([])
          }
        }}
      />
    </MenuHeader>

    {Object.keys(props.creatable).map(type =>
      <li key={type} className="dropdown-item" role="presentation">
        <Checkbox
          id={`${props.id}-${type}`}
          className="mb-0"
          switch={true}
          label={props.creatable[type]}
          disabled={!props.editable}
          checked={props.permission && -1 !== props.permission.indexOf(type)}
          onChange={(checked) => {
            if (checked) {
              props.onChange([].concat(props.permission || [], [type]))
            } else {
              const newPerm = props.permission ? props.permission.slice() : []
              newPerm.splice(newPerm.indexOf(type), 1)
              props.onChange(newPerm)
            }
          }}
        />
      </li>
    )}
  </ul>
)

CreateMenu.propTypes = {
  id: T.string,
  editable: T.bool,
  permission: T.array,
  creatable: T.object.isRequired,
  onChange: T.func.isRequired
}

const CreatePermission = props => {
  // be sure every creatable type is only declared once
  // (it can be duplicated because of some migrations)
  const permissions = uniq(props.permission || [])

  return (
    <MenuButton
      id={`${props.id}-rights-creation`}
      className={classes('py-1 px-2 border focus-ring btn btn-text-body', {
        'border-primary text-primary-emphasis bg-primary-subtle': permissions && 0 < permissions.length
      })}
      size="sm"
      menu={
        <Menu
          id={props.id}
          as={CreateMenu}
          align="end"
          permission={permissions}
          editable={props.editable}
          creatable={props.creatable}
          onChange={props.onChange}
        />
      }
    >
      {trans('create', {}, 'actions')}

      <Badge variant={isEmpty(permissions) ? 'secondary' : 'primary'} className="ms-1 p-1">
        {permissions ? permissions.length : 0}
      </Badge>
    </MenuButton>
  )
}

CreatePermission.propTypes = {
  id: T.string.isRequired,
  creatable: T.object.isRequired,
  editable: T.bool.isRequired,
  permission: T.array,
  onChange: T.func.isRequired
}

const RolePermissions = props => {
  const allPerms = Object.keys(props.permissions)
    .sort((permA, permB) => {
      if (get(props.permissions, permA+'.order') > get(props.permissions, permB+'.order')) {
        return 1
      }
      if (get(props.permissions, permB+'.order') > get(props.permissions, permA+'.order')) {
        return -1
      }

      return 0
    })

  return (
    <li className="list-group-item d-flex flex-row flex-wrap gap-3 px-0">
      <DataMicro object={{
        name: trans(props.translationKey)
      }} />

      <div className="ms-auto gap-1 d-flex flex-row fs-sm">
        {allPerms.map((permission) => {
          return (
            <TooltipOverlay key={permission} tip={(
              <ul className="w-100 text-start mb-0 ps-2">
                {props.permissions[permission].actions.map(action =>
                  <li key={action}>{action}</li>
                )}
              </ul>
            )}>
              {('create' !== permission || isEmpty(props.creatable)) ?
                <div role="presentation">
                  <input
                    className="btn-check"
                    type="checkbox"
                    id={`${permission}-${props.name}`}
                    checked={props.currentPermissions[permission]}
                    disabled={!props.editable}
                    onChange={() => props.update(merge({}, props.currentPermissions, {[permission]: !props.currentPermissions[permission]}))}
                  />

                  <label
                    className={classes('py-1 px-2 border focus-ring btn btn-text-body btn-sm', {
                      'border-primary text-primary-emphasis bg-primary-subtle': props.currentPermissions[permission]
                    })}
                    htmlFor={`${permission}-${props.name}`}
                  >
                    {trans(permission, {}, 'actions')}
                  </label>
                </div>
                :
                <CreatePermission
                  key={permission}
                  id={props.name}
                  permission={props.currentPermissions[permission]}
                  editable={props.editable}
                  creatable={props.creatable}
                  onChange={(creationPerms) => {
                    const newPerms = merge({}, props.currentPermissions)
                    newPerms.create = creationPerms

                    props.update(newPerms)
                  }}
                />
              }
            </TooltipOverlay>
          )}
        )}
      </div>

      <Button
        variant="btn btn-link mx-n2"
        type={CALLBACK_BUTTON}
        label={trans('remove', {}, 'actions')}
        callback={props.delete}
        disabled={!props.editable}
        size="sm"
      />
    </li>
  )
}

RolePermissions.propTypes = {
  name: T.string.isRequired,
  translationKey: T.string.isRequired,
  // the available permissions
  permissions: T.object.isRequired,
  currentPermissions: T.object.isRequired,
  editable: T.bool,
  creatable: T.object,
  update: T.func.isRequired,
  delete: T.func.isRequired
}

const ContentRights = props => {
  const [showHelp, setShowHelp] = useState(false)

  const allPerms = Object.keys(props.permissions)
    .sort((permA, permB) => {
      if (get(props.permissions, permA+'.order') > get(props.permissions, permB+'.order')) {
        return 1
      }
      if (get(props.permissions, permB+'.order') > get(props.permissions, permA+'.order')) {
        return -1
      }

      return 0
    })
  const defaultPerms = allPerms.reduce((acc, perm) => Object.assign(acc, {
    [perm]: false
  }), {})

  return (
    <>
      <div className="d-flex flex-row gap-1" role="presentation">
        <Button
          className="btn btn-primary"
          type={MODAL_BUTTON}
          label={trans('add_roles', {}, 'actions')}
          modal={[MODAL_ROLES, {
            contextType: !isEmpty(props.workspace) ? 'workspace' : 'desktop',
            contextId: get(props.workspace, 'id'),
            selectAction: (selectedRoles) => ({
              type: CALLBACK_BUTTON,
              callback: () => props.updateRights([].concat(props.rights, selectedRoles.map(role => ({
                name: role.name,
                translationKey: role.translationKey,
                role: role,
                permissions: {}
              }))))
            })
          }]}
        />

        <Button
          className="btn btn-text-body focus-ring"
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-question-circle"
          label={trans(showHelp ? 'hide_help' : 'show_help', {}, 'actions')}
          callback={() => setShowHelp(!showHelp)}
        />
      </div>

      {props.permissions &&
        <Collapse in={showHelp}>
          <dl className="p-4 mb-0 bg-body-tertiary rounded-3 gap-0">
            {allPerms.map(permission =>
              <Fragment key={permission}>
                <dt className="text-uppercase fw-bolder fs-base text-body">{trans(permission, {}, 'actions')}</dt>
                <dd className="mb-3">
                  <ul className="list-unstyled mb-0">
                    {props.permissions[permission].actions.map(action =>
                      <li key={action}>{action}</li>
                    )}
                  </ul>
                </dd>
              </Fragment>
            )}
          </dl>
        </Collapse>
      }

      {!isEmpty(props.rights) &&
        <ul className="list-group list-group-flush">
          {[]
            // create a new array to be able to modify it
            .concat(props.rights)
            // move the workspace manager role to the top of the list
            .sort((a, b) => props.workspace && roleWorkspace(props.workspace, true) === b.name ? 1 : 0)
            .map((rolePerm) => {
              let workspaceName
              if (props.workspace && rolePerm.workspace && rolePerm.workspace.id !== props.workspace.id) {
                workspaceName = rolePerm.workspace ? rolePerm.workspace.name : null
              }

              const displayName = trans(rolePerm.translationKey) + (workspaceName ? ' (' + workspaceName + ')' : '')
              let managerRole = null
              if (props.workspace) {
                managerRole = roleWorkspace(props.workspace, true)
              }

              return (
                <RolePermissions
                  key={rolePerm.id || rolePerm.name}
                  name={rolePerm.name}
                  translationKey={displayName}
                  permissions={props.permissions}
                  currentPermissions={Object.assign({}, defaultPerms, rolePerm.permissions)}
                  creatable={props.creatable}
                  editable={!managerRole || rolePerm.name !== managerRole}
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
        </ul>
      }
    </>
  )
}

ContentRights.propTypes = {
  creatable: T.object,
  workspace: T.shape({
    id: T.string.isRequired
  }),
  rights: T.arrayOf(T.shape({
    name: T.string.isRequired,
    translationKey: T.string.isRequired,
    permissions: T.object.isRequired
  })).isRequired,
  updateRights: T.func.isRequired,
  permissions: T.object.isRequired
}

export {
  ContentRights
}
