import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {ActivityCalendar} from '#/main/app/chart/activity-calendar/components/main'
import {trans} from '#/main/app/intl'
import {selectors} from '#/main/community/tools/community/activity/store'
import isEmpty from 'lodash/isEmpty'
import {LogFunctionalList} from '#/main/log/components/functional-list'

const Activity = (props) =>
  <>
    <ActivityCalendar />

    <LogFunctionalList
      className="component-container"
      name={selectors.STORE_NAME + '.logs'}
      url={props.url}
      customDefinition={[
        /*{
          name: 'workspace',
          type: 'workspace',
          label: trans('workspace'),
          displayable: isEmpty(this.props.contextId),
          displayed: isEmpty(this.props.contextId)
        }, {
          name: 'resource',
          type: 'resource',
          label: trans('resource'),
          displayed: true
        }*/
      ]}
    />

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
