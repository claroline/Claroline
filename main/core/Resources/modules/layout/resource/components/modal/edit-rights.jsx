import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'

import Modal from 'react-bootstrap/lib/Modal'
import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import Popover from 'react-bootstrap/lib/Popover'

import {t}         from '#/main/core/translation'
import {t_res}     from '#/main/core/layout/resource/translation'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'

import {
  getSimpleAccessRule,
  setSimpleAccessRule,
  hasCustomRules
} from '#/main/core/layout/resource/rights'

export const MODAL_RESOURCE_RIGHTS = 'MODAL_RESOURCE_RIGHTS'

const CreatePermission = props =>
  <td
    key="create-cell"
    className="create-cell"
  >
    <OverlayTrigger
      trigger="click"
      placement="left"
      rootClose={true}
      overlay={
        <Popover
          id="popover-positioned-top"
          className="popover-list-group"
          title={
            <label className="checkbox-inline">
              <input type="checkbox" />
              {t('resource_type')}
            </label>
          }
        >
          <ul className="list-group">
            {Object.keys(props.permission).map(resourceType =>
              <li key={resourceType} className="list-group-item">
                <label className="checkbox-inline">
                  <input
                    type="checkbox"
                    checked={props.permission[resourceType]}
                  />
                  {t_res(resourceType)}
                </label>
              </li>
            )}
          </ul>
        </Popover>
      }
    >
      <button type="button" className="btn btn-link-default">
        <span className="fa fa-fw fa-folder-open" />
      </button>
    </OverlayTrigger>
  </td>

CreatePermission.propTypes = {
  permission: T.object.isRequired
}

const RolePermissions = props =>
  <tr>
    <th scope="row">
      {t(props.role.key)}
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
  role: T.shape({
    key: T.string.isRequired
  }).isRequired,
  permissions: T.object.isRequired,
  updatePermissions: T.func.isRequired
}

const AdvancedTab = props =>
  <div>
    <table className="table table-striped table-hover resource-rights-advanced">
      <thead>
        <tr>
          <th scope="col">{t('role')}</th>
          {Object.keys(props.permissions['ROLE_USER'].permissions).map(permission =>
            ('create' !== permission || !isEmpty(props.permissions['ROLE_USER'].permissions[permission])) &&
            <th key={`${permission}-header`} scope="col">
              <div className="permission-name-container">
                <span className="permission-name">{t_res(permission)}</span>
              </div>
            </th>
          )}
        </tr>
      </thead>

      <tbody>
      {Object.keys(props.permissions).map(roleName =>
        <RolePermissions
          key={roleName}
          role={props.permissions[roleName].role}
          permissions={props.permissions[roleName].permissions}
          updatePermissions={(permissions) => props.updateRolePermissions(roleName, permissions)}
        />
      )}
      </tbody>
    </table>
  </div>

AdvancedTab.propTypes = {
  permissions: T.object.isRequired,
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
    <span className="simple-right-label">{t_res('resource_rights_'+props.mode)}</span>
  </button>

SimpleAccessRule.propTypes = {
  icon: T.string.isRequired,
  mode: T.string.isRequired,
  currentMode: T.string.isRequired,
  toggleMode: T.func.isRequired
}

const SimpleTab = props =>
  <Modal.Body>
    <p>{t_res('resource_access_rights')}</p>

    <div className="resource-rights-simple">
      <SimpleAccessRule mode="all" icon="fa fa-globe" {...props} />
      <SimpleAccessRule mode="user" icon="fa fa-users" {...props} />
      <SimpleAccessRule mode="workspace" icon="fa fa-book" {...props} />
      <SimpleAccessRule mode="admin" icon="fa fa-lock" {...props} />
    </div>

    {props.customRules &&
      <p className="resource-custom-rules-info">
        <span className="fa fa-asterisk" />
        {t_res('resource_rights_custom_help')}
      </p>
    }
  </Modal.Body>

SimpleTab.propTypes = {
  currentMode: T.string,
  customRules: T.bool,
  toggleMode: T.func.isRequired
}

SimpleTab.defaultProps = {
  currentMode: '',
  customRules: false
}

class EditRightsModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      activeTab: 'simple',
      pendingChanges: false,
      currentMode: getSimpleAccessRule(this.props.resourceNode.rights.all.permissions, this.props.resourceNode.workspace),
      customRules: hasCustomRules(this.props.resourceNode.rights.all.permissions, this.props.resourceNode.workspace),
      rights: Object.assign({}, this.props.resourceNode.rights.all)
    }

    this.toggleSimpleMode = this.toggleSimpleMode.bind(this)
    this.updateRolePermissions = this.updateRolePermissions.bind(this)
    this.save = this.save.bind(this)
  }

  toggleSimpleMode(mode) {
    const newPermissions = setSimpleAccessRule(this.state.rights.permissions, mode, this.props.resourceNode.workspace)

    this.updatePermissions(newPermissions)
  }

  updateRolePermissions(roleName, permissions) {
    const newPermissions = merge({}, this.state.rights.permissions, {
      [roleName]: merge({}, this.state.rights.permissions[roleName], {
        permissions: permissions
      })
    })

    this.updatePermissions(newPermissions)
  }

  updatePermissions(permissions) {
    this.setState({
      pendingChanges: true,
      currentMode: getSimpleAccessRule(permissions, this.props.resourceNode.workspace),
      customRules: hasCustomRules(permissions, this.props.resourceNode.workspace),
      rights: Object.assign({}, this.state.rights, {
        permissions: permissions
      })
    })
  }

  save() {
    this.props.save(merge({}, this.props.resourceNode, {rights: {all: this.state.rights}}))
    this.props.fadeModal()
  }

  render() {
    return (
      <BaseModal
        icon="fa fa-fw fa-lock"
        title={t_res('edit-rights')}
        className="resource-edit-rights-modal"
        {...this.props}
      >
        <ul className="nav nav-tabs">
          <li className={classes({active: 'simple' === this.state.activeTab})}>
            <a
              role="button"
              href=""
              onClick={(e) => {
                e.preventDefault()
                this.setState({activeTab: 'simple'})
              }}
            >
              {t('simple')}
            </a>
          </li>

          <li className={classes({active: 'advanced' === this.state.activeTab})}>
            <a
              role="button"
              href=""
              onClick={(e) => {
                e.preventDefault()
                this.setState({activeTab: 'advanced'})
              }}
            >
              {t('advanced')}
            </a>
          </li>
        </ul>

        {'simple' === this.state.activeTab &&
          <SimpleTab
            currentMode={this.state.currentMode}
            customRules={this.state.customRules}
            toggleMode={this.toggleSimpleMode}
          />
        }

        {'advanced' === this.state.activeTab &&
          <AdvancedTab
            permissions={this.state.rights.permissions}
            updateRolePermissions={this.updateRolePermissions}
          />
        }

        <button
          className="modal-btn btn btn-primary"
          disabled={!this.state.pendingChanges}
          onClick={this.save}
        >
          {t('save')}
        </button>
      </BaseModal>
    )
  }
}

EditRightsModal.propTypes = {
  resourceNode: T.shape({
    workspace: T.shape({
      id: T.string.isRequired
    }),
    rights: T.shape({
      all: T.shape({
        decoders: T.array.isRequired,
        permissions: T.object.isRequired
      }).isRequired
    }).isRequired
  }).isRequired,
  fadeModal: T.func.isRequired,
  save: T.func.isRequired
}

export {
  EditRightsModal
}
