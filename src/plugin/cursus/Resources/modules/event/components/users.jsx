import React, {useMemo} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'
import merge from 'lodash/merge'

import {selectors as securitySelectors} from '#/main/app/security'
import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {Badge} from '#/main/app/components/badge'
import {actions as listActions} from '#/main/app/content/list'

import {constants} from '#/plugin/cursus/constants'
import {RegistrationUsers} from '#/plugin/cursus/registration/components/users'
import {getRegistrationActions, getRegistrationDefaultAction} from '#/plugin/cursus/event/utils'
import {MODAL_EVIDENCE_ABOUT} from '#/plugin/cursus/presence/modals/about'

const EventUsers = (props) => {
  const dispatch = useDispatch()
  const currentUser = useSelector(securitySelectors.currentUser)

  const refresher = useMemo(() => merge({
    add:    () => dispatch(listActions.invalidateData(props.name)),
    update: () => dispatch(listActions.invalidateData(props.name)),
    delete: () => dispatch(listActions.invalidateData(props.name))
  }, props.refresher || {}), [props.path])

  return (
    <RegistrationUsers
      {...props}
      name={props.name}
      url={props.url}
      customDefinition={[
        {
          name: 'presence.status',
          type: 'choice',
          label: trans('presence'),
          options: {
            choices: constants.PRESENCE_STATUSES
          },
          render: (row) => (
            <Badge variant={constants.PRESENCE_STATUS_COLORS[get(row, 'presence.status', constants.PRESENCE_STATUS_UNKNOWN)]}>
              {constants.PRESENCE_STATUSES[get(row, 'presence.status', constants.PRESENCE_STATUS_UNKNOWN)]}
            </Badge>
          ),
          displayed: true,
          filterable: false,
          sortable: false
        }, {
          name: 'presence.meta.updatedBy',
          type: 'user',
          label: trans('updated_by', {}, 'presence'),
          filterable: false,
          sortable: false
        }, {
          name: 'presence.meta.updatedAt',
          type: 'date',
          label: trans('updated_at', {}, 'presence'),
          filterable: false,
          sortable: false,
          options: {time: true}
        },{
          name: 'presence.validation_date',
          type: 'date',
          label: trans('presence_confirmation_date', {}, 'presence'),
          filterable: false,
          sortable: false,
          options: {time: true}
        }, {
          name: 'evidences',
          type: 'number',
          label: trans('show_evidence', {}, 'presence'),
          filterable: false,
          sortable: false,
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
        }
      ].concat(props.customDefinition || [])}
      primaryAction={(row) => getRegistrationDefaultAction(row, refresher, props.path, currentUser)}
      actions={(rows) => getRegistrationActions(rows, refresher, props.path, currentUser)}
    />
  )
}

EventUsers.propTypes = {
  path: T.string.isRequired,
  url: T.oneOfType([T.array, T.string]),
  name: T.string.isRequired,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  })),
  refresher: T.shape({
    add: T.func,
    update: T.func,
    delete: T.func
  })
}

export {
  EventUsers
}
