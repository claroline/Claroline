import React, {Fragment} from 'react'
import {useDispatch} from 'react-redux'

import {ContentTitle} from '#/main/app/content/components/title'
import {Alert} from '#/main/app/alert/components/alert'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {actions as alertActions} from '#/main/app/overlays/alert/store'
import {constants as alertConstants} from '#/main/app/overlays/alert/constants'
import {constants as actionConstants} from '#/main/app/action/constants'

const ExampleAlerts = () => {
  const dispatch = useDispatch()

  return (
    <Fragment>
      <ContentTitle title="Flying alerts" />

      <div className="btn-toolbar gap-1 mb-3">
        <Button
          type={CALLBACK_BUTTON}
          className="btn btn-secondary"
          label="Pending"
          callback={() => dispatch(alertActions.addAlert(
            'ALERT_EXAMPLE_PENDING',
            alertConstants.ALERT_STATUS_PENDING,
            actionConstants.ACTION_SAVE
          ))}
        />

        <Button
          type={CALLBACK_BUTTON}
          className="btn btn-success"
          label="Success"
          callback={() => dispatch(alertActions.addAlert(
            'ALERT_EXAMPLE_SUCCESS',
            alertConstants.ALERT_STATUS_SUCCESS,
            actionConstants.ACTION_SAVE
          ))}
        />

        <Button
          type={CALLBACK_BUTTON}
          className="btn btn-warning"
          label="Warning"
          callback={() => dispatch(alertActions.addAlert(
            'ALERT_EXAMPLE_WARNING',
            alertConstants.ALERT_STATUS_WARNING,
            actionConstants.ACTION_SAVE
          ))}
        />

        <Button
          type={CALLBACK_BUTTON}
          className="btn btn-danger"
          label="Error"
          callback={() => dispatch(alertActions.addAlert(
            'ALERT_EXAMPLE_ERROR',
            alertConstants.ALERT_STATUS_ERROR,
            actionConstants.ACTION_SAVE
          ))}
        />

        <Button
          type={CALLBACK_BUTTON}
          className="btn btn-info"
          label="Info"
          callback={() => dispatch(alertActions.addAlert(
            'ALERT_EXAMPLE_INFO',
            alertConstants.ALERT_STATUS_INFO,
            actionConstants.ACTION_SCHEDULE
          ))}
        />
      </div>

      <ContentTitle title="Simple alerts" />
      {['success', 'danger', 'warning', 'info'].map(type =>
        <Alert key={type} type={type}>
          A simple {type} alert—check it out!
        </Alert>
      )}

      <ContentTitle title="Detailled alerts" />
      {['success', 'danger', 'warning', 'info'].map(type =>
        <Alert key={type} type={type} title={`My ${type} alert title`}>
          A simple {type} alert—check it out! This example text is going to run a bit longer so that you can see how spacing within an alert works with this kind of content.
          <hr/>
          More text content related to the message.
        </Alert>
      )}

      <ContentTitle title="Alerts with button" />
      {['success', 'danger', 'warning', 'info'].map(type =>
        <Alert key={type} type={type} title={`My ${type} alert title`}>
          A simple {type} alert—check it out! This example text is going to run a bit longer so that you can see how spacing within an alert works with this kind of content.
          <div className="btn-toolbar gap-1 mt-3 justify-content-end">
            <Button
              className={`btn btn-${type}`}
              label="My button"
              type={CALLBACK_BUTTON}
              callback={() => true}
            />
            <Button
              className={`btn btn-outline-${type}`}
              label="My another button"
              type={CALLBACK_BUTTON}
              callback={() => true}
            />
          </div>
        </Alert>
      )}
    </Fragment>
  )
}

export {
  ExampleAlerts
}
