import React, {Fragment, Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import moment from 'moment'
import {schemeCategory20c} from 'd3-scale'

import {trans} from '#/main/app/intl/translation'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {ListData} from '#/main/app/content/list/containers/data'
import {Checkbox} from '#/main/app/input/components/checkbox'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentCounter} from '#/main/app/content/components/counter'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_USERS} from '#/main/core/modals/users'
import {MODAL_GROUPS} from '#/main/core/modals/groups'
import {selectors as baseSelectors} from '#/main/core/administration/community/store'
import {constants} from '#/main/core/user/constants'
import {Role as RoleTypes} from '#/main/core/user/prop-types'
import {GroupList} from '#/main/core/administration/community/group/components/group-list'
import {UserList} from '#/main/core/user/components/list'

// TODO : merge with main/core/tools/community/role/components/role

class RoleInfo extends Component {
  constructor(props) {
    super(props)

    this.state = {
      loaded: false,
      current: moment().year(),
      available: [
        moment().year(),
        moment().year() - 1,
        moment().year() - 2
      ],
      count: {
        users: 20,
        connections: 50,
        actions: 90
      }
    }
  }

  componentDidMount() {
    this.props.load(this.state.current).then((response) => this.setState({
      count: response,
      loaded: true
    }))
  }

  changeYear(year) {
    this.setState({
      loaded: false,
      current: year
    })

    // reload
    this.props.load(year).then((response) => this.setState({
      count: response,
      loaded: true
    }))
  }

  render() {
    let ellapsedDays = 365
    if (this.state.current === moment().year()) {
      // current period
      ellapsedDays = moment().diff(moment([this.state.current, '01, 01']), 'days') + 1
    }

    return (
      <Fragment>
        <div className="row" style={{display: 'flex', alignItems: 'center'}}>
          <Toolbar
            buttonName="btn-block"
            style={{marginLeft: '15px'}}
            toolbar={this.state.available.map(year => 'y'+year).join(' ')}
            size="xs"
            actions={this.state.available.map(year => (
              {
                className: year === this.state.current ? 'btn' : 'btn-link',
                name: 'y'+year,
                type: CALLBACK_BUTTON,
                label: year,
                callback: () => this.changeYear(year)
              }
            ))}
          />

          <ContentCounter
            icon="fa fa-user"
            label={trans('users')}
            color={schemeCategory20c[1]}
            value={!this.state.loaded ? '?' : this.state.count.users}
            help={trans('role_analytics_users_help', {}, 'user')}
          />

          <ContentCounter
            icon="fa fa-power-off"
            label={trans('connections')}
            color={schemeCategory20c[5]}
            value={!this.state.loaded ?
              '? ' :
              this.state.count.connections + ' (' + Math.ceil(this.state.count.connections / ellapsedDays) + ' ' + trans('per_day_short') + ')'}
            help={trans('role_analytics_connections_help', {}, 'user')}
          />

          <ContentCounter
            icon="fa fa-history"
            label={trans('actions')}
            color={schemeCategory20c[9]}
            value={!this.state.loaded ?
              '? ' :
              this.state.count.actions + ' (' + Math.ceil(this.state.count.actions / ellapsedDays) + ' ' + trans('per_day_short') + ')'}
            help={trans('role_analytics_actions_help', {}, 'user')}
          />
        </div>
      </Fragment>
    )
  }
}


RoleInfo.propTypes = {
  load: T.func.isRequired
}

const Role = props => {
  if (!props.new && isEmpty(props.role)) {
    return (
      <ContentLoader
        className="row"
        size="lg"
        description="Nour chargeons le rôle...."
      />
    )
  }

  return (
    <Fragment>
      {!props.new &&
        <RoleInfo
          load={(year) => props.loadStatistics(props.role.id, year)}
        />
      }

      <FormData
        level={3}
        name={`${baseSelectors.STORE_NAME}.roles.current`}
        buttons={true}
        target={(role, isNew) => isNew ?
          ['apiv2_role_create'] :
          ['apiv2_role_update', {id: role.id}]
        }
        cancel={{
          type: LINK_BUTTON,
          target: props.path+'/roles',
          exact: true
        }}
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
              }, {
                name: 'type',
                type: 'choice',
                label: trans('type'),
                disabled: !props.new,
                required: true,
                options: {
                  condensed: true,
                  choices: constants.ROLE_TYPES
                },
                onChange: (value) => {
                  if (constants.ROLE_WORKSPACE !== value) {
                    props.updateProp('workspace', null)
                  }

                  if (constants.ROLE_USER !== props.role.type) {
                    props.updateProp('user', null)
                  }
                },
                linked: [
                  {
                    name: 'workspace',
                    type: 'workspace',
                    label: trans('workspace'),
                    required: true,
                    disabled: !props.new,
                    displayed: constants.ROLE_WORKSPACE === props.role.type
                  }, {
                    name: 'user',
                    type: 'user',
                    label: trans('user'),
                    required: true,
                    disabled: !props.new,
                    displayed: constants.ROLE_USER === props.role.type
                  }
                ]
              }
            ]
          }, {
            icon: 'fa fa-fw fa-book',
            title: trans('workspace'),
            fields: [
              {
                name: 'meta.personalWorkspaceCreationEnabled',
                type: 'boolean',
                label: trans('role_personalWorkspaceCreation'),
                help: trans('role_personalWorkspaceCreation_help')
              }
            ]
          }
        ]}
      />

      <FormSections
        level={3}
      >
        {constants.ROLE_PLATFORM === props.role.type &&
          <FormSection
            icon="fa fa-fw fa-cogs"
            title={trans('administration_tools')}
          >
            <div className="list-group" fill={true}>
              {Object.keys(props.role.adminTools || {}).map(toolName =>
                <Checkbox
                  key={toolName}
                  id={toolName}
                  className={classes('list-group-item', {
                    'list-group-item-selected': props.role.adminTools[toolName]
                  })}
                  label={trans(toolName, {}, 'tools')}
                  checked={props.role.adminTools[toolName]}
                  onChange={checked => props.updateProp(`adminTools.${toolName}`, checked)}
                />
              )}
            </div>
          </FormSection>
        }

        {constants.ROLE_PLATFORM === props.role.type &&
          <FormSection
            icon="fa fa-fw fa-tools"
            title={trans('desktop_tools')}
          >
            <div className="list-group" fill={true}>
              {Object.keys(props.role.desktopTools || {}).map(toolName =>
                <div key={toolName} className="tool-rights-row list-group-item">
                  <div className="tool-rights-title">
                    {trans(toolName, {}, 'tools')}
                  </div>

                  <div className="tool-rights-actions">
                    {Object.keys(props.role.desktopTools[toolName]).map((permName) =>
                      <Checkbox
                        key={permName}
                        id={`${toolName}-${permName}`}
                        label={trans(permName, {}, 'actions')}
                        checked={props.role.desktopTools[toolName][permName]}
                        onChange={checked => props.updateProp(`desktopTools.${toolName}.${permName}`, checked)}
                      />
                    )}
                  </div>
                </div>
              )}
            </div>
          </FormSection>
        }

        <FormSection
          className="embedded-list-section"
          icon="fa fa-fw fa-user"
          title={trans('users')}
          disabled={!props.role.id || props.new}
          actions={[
            {
              name: 'add-users',
              type: MODAL_BUTTON,
              icon: 'fa fa-fw fa-plus',
              label: trans('add_user'),
              modal: [MODAL_USERS, {
                selectAction: (selected) => ({
                  type: CALLBACK_BUTTON,
                  label: trans('add', {}, 'actions'),
                  callback: () => props.addUsers(props.role.id, selected)
                })
              }]
            }
          ]}
        >
          {props.role.id && !props.new &&
            <UserList
              name={`${baseSelectors.STORE_NAME}.roles.current.users`}
              url={['apiv2_role_list_users', {id: props.role.id}]}
              primaryAction={(row) => ({
                type: LINK_BUTTON,
                target: `${props.path}/users/form/${row.id}`,
                label: trans('edit', {}, 'actions')
              })}
              delete={{
                url: ['apiv2_role_remove_users', {id: props.role.id}]
              }}
            />
          }
        </FormSection>

        <FormSection
          className="embedded-list-section"
          icon="fa fa-fw fa-id-badge"
          title={trans('groups')}
          disabled={props.new}
          actions={[
            {
              name: 'add-groups',
              type: MODAL_BUTTON,
              icon: 'fa fa-fw fa-plus',
              label: trans('add_group'),
              modal: [MODAL_GROUPS, {
                selectAction: (selected) => ({
                  type: CALLBACK_BUTTON,
                  label: trans('add', {}, 'actions'),
                  callback: () => props.addGroups(props.role.id, selected)
                })
              }]
            }
          ]}
        >
          <ListData
            name={`${baseSelectors.STORE_NAME}.roles.current.groups`}
            fetch={{
              url: ['apiv2_role_list_groups', {id: props.role.id}],
              autoload: props.role.id && !props.new
            }}
            primaryAction={(row) => ({
              type: LINK_BUTTON,
              target: `${props.path}/groups/form/${row.id}`,
              label: trans('edit', {}, 'actions')
            })}
            delete={{
              url: ['apiv2_role_remove_groups', {id: props.role.id}]
            }}
            definition={GroupList.definition}
            card={GroupList.card}
          />
        </FormSection>
      </FormSections>
    </Fragment>
  )
}

Role.propTypes = {
  path: T.string.isRequired,
  new: T.bool.isRequired,
  role: T.shape(
    RoleTypes.propTypes
  ).isRequired,
  updateProp: T.func.isRequired,
  addUsers: T.func.isRequired,
  addGroups: T.func.isRequired,
  loadStatistics: T.func.isRequired
}

export {
  Role
}
