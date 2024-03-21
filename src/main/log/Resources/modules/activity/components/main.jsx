import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {ActivityCalendar} from '#/main/app/chart/activity-calendar/components/main'
import {trans} from '#/main/app/intl'


const Activity = (props) =>
  <>
    <ActivityCalendar />

    <Button
      className="btn btn-outline-primary w-100 mt-3"
      type={CALLBACK_BUTTON}
      label={trans('Voir plus d\'activités')}
      callback={() => true}
    />
  </>

Activity.propTypes = {
  url: T.oneOfType([T.string, T.array])
}

export {
  Activity
}
