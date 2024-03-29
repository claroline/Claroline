import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {getActions, getDefaultAction} from '#/main/community/team/utils'
import {TeamCard} from '#/main/community/team/components/card'

const Teams = props => {
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
          name: 'name',
          type: 'string',
          label: trans('name'),
          displayed: true,
          primary: true
        }, {
          name: 'meta.description',
          type: 'string',
          label: trans('description'),
          options: {long: true},
          displayed: true,
          sortable: false
        }, {
          name: 'users',
          type: 'number',
          label: trans('users'),
          displayed: true,
          filterable: false
        }, {
          name: 'directory',
          type: 'resource',
          label: trans('directory', {}, 'resource'),
          sortable: false,
          filterable: false
        }, {
          name: 'registration.selfRegistration',
          label: trans('public_registration'),
          displayed: false,
          filterable: true,
          type: 'boolean'
        }, {
          name: 'registration.selfUnregistration',
          label: trans('public_unregistration'),
          displayed: false,
          filterable: true,
          type: 'boolean'
        }
      ].concat(props.customDefinition)}

      {...omit(props, 'path', 'url', 'autoload', 'customDefinition', 'customActions', 'refresher', 'invalidate')}

      name={props.name}
      fetch={{
        url: props.url,
        autoload: props.autoload
      }}
      card={TeamCard}
    />
  )
}

Teams.propTypes = {
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

Teams.defaultProps = {
  autoload: true,
  customDefinition: [],
  customActions: () => []
}

const TeamList = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  }),
  (dispatch) => ({
    invalidate(name) {
      dispatch(listActions.invalidateData(name))
    }
  })
)(Teams)

export {
  TeamList
}
