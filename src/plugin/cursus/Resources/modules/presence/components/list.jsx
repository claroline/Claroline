import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import merge from 'lodash/merge'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as listActions} from '#/main/app/content/list/store'

import {getActions, getDefaultAction} from '#/plugin/cursus/presence/utils'
import {constants} from '#/plugin/cursus/constants'

const Presences = props => {
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
          name: 'user',
          type: 'user',
          label: trans('user'),
          displayed: true
        }, {
          name: 'status',
          type: 'choice',
          label: trans('status'),
          options: {
            choices: constants.PRESENCE_STATUSES
          },
          render: (row) => (
            <span className={classes('badge', `text-bg-${constants.PRESENCE_STATUS_COLORS[row.status]}`)}>
              {constants.PRESENCE_STATUSES[row.status]}
            </span>
          ),
          displayed: true
        }, {
          name: 'userDisabled',
          label: trans('user_disabled', {}, 'community'),
          type: 'boolean',
          displayable: false,
          sortable: false,
          filterable: true
        }
      ].concat(props.customDefinition)}

      {...omit(props, 'path', 'url', 'autoload', 'customDefinition', 'customActions', 'refresher', 'invalidate')}

      name={props.name}
      fetch={{
        url: props.url,
        autoload: props.autoload
      }}
    />
  )
}

Presences.propTypes = {
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,

  path: T.string,
  autoload: T.bool,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  })),
  customActions: T.func,
  refresher: T.shape({
    add: T.func,
    update: T.func,
    delete: T.func
  }),
  // from store
  invalidate: T.func.isRequired,
  currentUser: T.object
}

Presences.defaultProps = {
  autoload: true,
  customDefinition: [],
  customActions: () => []
}

const PresencesList = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  }),
  (dispatch) => ({
    invalidate(name) {
      dispatch(listActions.invalidateData(name))
    }
  })
)(Presences)

export {
  PresencesList
}
