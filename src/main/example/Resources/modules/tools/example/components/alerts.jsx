import React, {Fragment} from 'react'

import {ContentTitle} from '#/main/app/content/components/title'
import {Alert} from '#/main/app/alert/components/alert'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

const ExampleAlerts = () =>
  <Fragment>
    <ContentTitle title="Simple alerts" />
    {['primary', 'secondary', 'success', 'danger', 'warning', 'info'].map(type =>
      <Alert key={type} type={type}>
        A simple {type} alert—check it out!
      </Alert>
    )}

    <ContentTitle title="Detailled alerts" />
    {['primary', 'secondary', 'success', 'danger', 'warning', 'info'].map(type =>
      <Alert key={type} type={type} title={`My ${type} alert title`}>
        A simple {type} alert—check it out! This example text is going to run a bit longer so that you can see how spacing within an alert works with this kind of content.
        <hr/>
        More text content related to the message.
      </Alert>
    )}

    <ContentTitle title="Alerts with button" />
    {['primary', 'secondary', 'success', 'danger', 'warning', 'info'].map(type =>
      <Alert key={type} type={type} title={`My ${type} alert title`}>
        A simple {type} alert—check it out! This example text is going to run a bit longer so that you can see how spacing within an alert works with this kind of content.
        <br/><br/>
        <Button
          className={`btn ${type}`}
          label="My button"
          type={CALLBACK_BUTTON}
          callback={() => true}
        />
        <Button
          className={`btn default`}
          label="My another button"
          type={CALLBACK_BUTTON}
          callback={() => true}
        />
      </Alert>
    )}
  </Fragment>

export {
  ExampleAlerts
}
