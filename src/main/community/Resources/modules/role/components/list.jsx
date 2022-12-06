import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {getActions, getDefaultAction} from '#/main/community/role/utils'
import {RoleCard} from '#/main/community/role/components/card'
import {constants} from '#/main/community/constants'

const RoleListComponent = props => {
  const refresher = merge({
    add:    () => props.invalidate(props.name),
    update: () => props.invalidate(props.name),
    delete: () => props.invalidate(props.name)
  }, props.refresher || {})

  return (
    <ListData
      primaryAction={(row) => getDefaultAction(row, refresher, props.path, props.currentUser)}
      actions={(rows) => getActions(rows, refresher, props.path, props.currentUser).then((actions) => [].concat(actions, props.customActions(rows)))}
      definition={[
        {
          name: 'translationKey',
          type: 'translation',
          label: trans('name'),
          displayed: true,
          primary: true/*,
          calculated: (row) => {
            const workspaceCode = row.workspace ? row.workspace.code : null

            return  trans(row.translationKey) + (workspaceCode ? ' (' + workspaceCode + ')' : '')
          }*/
        }, {
          name: 'name',
          type: 'string',
          label: trans('code'),
          displayed: false
        }, {
          name: 'type',
          type: 'choice',
          label: trans('type'),
          options: {
            choices: constants.ROLE_TYPES
          },
          displayed: true
        }, {
          name: 'meta.description',
          type: 'string',
          label: trans('description'),
          options: {long: true},
          displayed: true,
          sortable: false
        }, {
          name: 'workspace',
          type: 'workspace',
          label: trans('workspace')
        }, {
          name: 'user',
          type: 'user',
          label: trans('user'),
          filterable: false,
          options: {
            placeholder: false
          }
        }
      ].concat(props.customDefinition)}

      {...omit(props, 'path', 'url', 'autoload', 'customDefinition', 'customActions', 'refresher', 'invalidate')}

      name={props.name}
      fetch={{
        url: props.url,
        autoload: props.autoload
      }}
      card={RoleCard}
    />
  )
}

RoleListComponent.propTypes = {
  path: T.string,
  name: T.string.isRequired,
  autoload: T.bool,
  url: T.oneOfType([T.string, T.array]).isRequired,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  })),
  customActions: T.func,
  invalidate: T.func.isRequired,
  currentUser: T.object,
  refresher: T.shape({
    add: T.func,
    update: T.func,
    delete: T.func
  })
}

RoleListComponent.defaultProps = {
  autoload: true,
  customDefinition: [],
  customActions: () => []
}

const RoleList = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  }),
  (dispatch) => ({
    invalidate(name) {
      dispatch(listActions.invalidateData(name))
    }
  })
)(RoleListComponent)

export {
  RoleList
}
