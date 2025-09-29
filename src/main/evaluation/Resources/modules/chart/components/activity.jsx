import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {useFetch} from '#/main/app/api/fetch'
import {trans, transChoice} from '#/main/app/intl'
import {CALLBACK_BUTTON, MenuButton, ModalButton} from '#/main/app/buttons'

import {ActivityCalendar} from '#/main/log/activity/components/calendar'
import {MODAL_VIEWERS} from '#/main/app/modals/viewers'
import {MODAL_FUNCTIONAL_LOGS} from '#/main/log/modals/functional'

const activityTypes = {
  actions: {
    icon: 'fa fa-fw fa-hand-pointer',
    label: trans('actions')
  },
  visitors: {
    icon: 'fa fa-fw fa-user',
    label: trans('visitors')
  },
  views: {
    icon: 'fa fa-fw fa-eye',
    label: trans('views'),
  }
}

const ActivityChart = ({className, name, activityUrl, logUrl, viewUrl}) => {
  const [activityType, setActivityType] = useState('views')
  const [activityData] = useFetch(name, activityUrl(activityType))

  return (
    <div className={classes('d-flex flex-column', className)}>
      <div className="d-flex flex-row align-items-center gap-3 mb-4">
        <h3 className="h5 mb-0 me-auto">
          Activité récente
        </h3>

        <MenuButton
          className="btn btn-body"
          size="sm"
          menu={{
            align: 'end',
            items: Object.keys(activityTypes).map(type => ({
              name: type,
              type: CALLBACK_BUTTON,
              icon: activityTypes[type].icon,
              label: activityTypes[type].label,
              active: activityType === type,
              callback: () => {
                setActivityType(type)
              }
            }))
          }}
        >
          {activityTypes[activityType].label}

          <small className="ms-1 fa fa-chevron-down opacity-50" aria-hidden={true} />
        </MenuButton>
      </div>

      <ActivityCalendar
        className="mx-auto"
        data={activityData}
        label={(date, count) => transChoice('count_'+activityType, count, {count: count, date: date})}
      />

      <ModalButton
        className="btn btn-link ms-auto mt-auto me-n3 mb-n2"
        modal={'actions' === activityType ? [
          MODAL_FUNCTIONAL_LOGS, {
            url: logUrl
          }
        ] : [
          MODAL_VIEWERS, {
            url: viewUrl
          }
        ]}
      >
        {trans('see_all', {}, 'actions')}
        <span className="ms-2 fa fa-arrow-right" aria-hidden="true" />
      </ModalButton>
    </div>
  )
}

ActivityChart.propTypes = {
  className: T.string,
  activityUrl: T.func.isRequired,
  // an API url to get the list of logs
  logUrl: T.oneOfType([T.string, T.array]).isRequired,
  // an API url to get the list of views
  viewUrl: T.oneOfType([T.string, T.array]).isRequired
}

export {
  ActivityChart
}
