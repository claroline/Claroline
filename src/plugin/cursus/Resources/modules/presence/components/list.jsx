import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import merge from 'lodash/merge'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {constants} from '#/plugin/cursus/constants'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {actions as listActions} from '#/main/app/content/list/store'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {getActions, getDefaultAction} from '#/plugin/cursus/presence/utils'
import {MODAL_EVIDENCE_ABOUT} from '#/plugin/cursus/modals/presence/about'

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
          name: 'presence_updated_by',
          type: 'user',
          label: trans('presence_updated_by', {}, 'presence'),
          displayed: true
        }, {
          name: 'presence_updated_at',
          type: 'date',
          label: trans('presence_updated_at', {}, 'presence'),
          displayed: true,
          options: {
            time: true
          }
        },{
          name: 'validation_date',
          type: 'date',
          label: trans('presence_confirmation_date', {}, 'presence'),
          displayed: true,
          options: {
            time: true
          }
        }, {
          name: 'evidences',
          type: 'number',
          label: trans('show_evidence', {}, 'presence'),
          displayed: true,
          render: (row) => {
            if (row.evidences && row.evidences.length === 1) {
              return (
                <Button
                  className="btn btn-link"
                  type={MODAL_BUTTON}
                  label={trans('show_evidence', {}, 'presence')}
                  modal={[MODAL_EVIDENCE_ABOUT, {presence: row}]}
                />
              )
            } else {
              return trans('no_evidence', {}, 'presence')
            }
          }
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
