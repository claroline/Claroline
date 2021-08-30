import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'
import get from 'lodash/get'

import {now} from '#/main/app/intl'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {SessionCard} from '#/plugin/cursus/session/components/card'
import {Session as SessionTypes} from '#/plugin/cursus/prop-types'

import {selectors} from '#/plugin/cursus/modals/sessions/store'

class SessionsModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      initialized: false
    }
  }

  render() {
    const selectAction = this.props.selectAction(this.props.selected)

    return (
      <Modal
        {...omit(this.props, 'url', 'selected', 'selectAction', 'reset', 'resetFilters')}
        icon="fa fa-fw fa-calendar-week"
        className="data-picker-modal"
        bsSize="lg"
        onEnter={() => {
          this.props.resetFilters(this.props.filters)
          this.setState({initialized: true})
        }}
        onExited={this.props.reset}
      >
        <ListData
          name={selectors.STORE_NAME}
          fetch={{
            url: this.props.url,
            autoload: this.state.initialized
          }}
          definition={[
            {
              name: 'status',
              type: 'choice',
              label: trans('status'),
              displayed: true,
              order: 1,
              options: {
                noEmpty: true,
                choices: {
                  not_started: trans('session_not_started', {}, 'cursus'),
                  in_progress: trans('session_in_progress', {}, 'cursus'),
                  ended: trans('session_ended', {}, 'cursus'),
                  not_ended: trans('session_not_ended', {}, 'cursus')
                }
              },
              render: (row) => {
                let status
                if (get(row, 'restrictions.dates[0]') > now(false)) {
                  status = 'not_started'
                } else if (get(row, 'restrictions.dates[0]') <= now(false) && get(row, 'restrictions.dates[1]') >= now(false)) {
                  status = 'in_progress'
                } else if (get(row, 'restrictions.dates[1]') < now(false)) {
                  status = 'ended'
                }

                const SessionStatus = (
                  <span className={classes('label', {
                    'label-success': 'not_started' === status,
                    'label-info': 'in_progress' === status,
                    'label-danger': 'ended' === status
                  })}>
                    {trans('session_'+status, {}, 'cursus')}
                  </span>
                )

                return SessionStatus
              }
            }, {
              name: 'name',
              type: 'string',
              label: trans('name'),
              displayed: true,
              primary: true
            }, {
              name: 'code',
              type: 'string',
              label: trans('code'),
              displayed: true
            }, {
              name: 'location',
              type: 'location',
              label: trans('location'),
              placeholder: trans('online_session', {}, 'cursus'),
              displayed: true
            }, {
              name: 'restrictions.dates[0]',
              alias: 'startDate',
              type: 'date',
              label: trans('start_date'),
              displayed: true
            }, {
              name: 'restrictions.dates[1]',
              alias: 'endDate',
              type: 'date',
              label: trans('end_date'),
              displayed: true
            }
          ]}
          card={SessionCard}
        />

        <Button
          label={trans('select', {}, 'actions')}
          {...selectAction}
          className="modal-btn btn"
          primary={true}
          disabled={0 === this.props.selected.length}
          onClick={this.props.fadeModal}
        />
      </Modal>
    )
  }
}

SessionsModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  filters: T.arrayOf(T.object),
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,

  // from store
  selected: T.arrayOf(T.shape(SessionTypes.propTypes)).isRequired,
  reset: T.func.isRequired,
  resetFilters: T.func.isRequired
}

SessionsModal.defaultProps = {
  url: ['apiv2_cursus_session_list'],
  title: trans('training_sessions', {}, 'cursus'),
  filters: []
}

export {
  SessionsModal
}
