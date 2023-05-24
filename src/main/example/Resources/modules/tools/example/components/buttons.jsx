import React, {Fragment} from 'react'
import classes from 'classnames'

import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

const ExampleButtons = () =>
  <Fragment>
    <div>
      {['primary', 'secondary', 'success', 'danger', 'warning', 'info'].map(type =>
        <Button
          key={type}
          className={classes('btn', type)}
          type={CALLBACK_BUTTON}
          label={type}
          callback={() => true}
        />
      )}
    </div>

    <div>
      <Button
        className="btn btn-primary"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-bomb"
        label={'Tooltip'}
        tooltip="bottom"
        callback={() => true}
      />
    </div>

    <div>
      <Button
        className="btn btn-primary"
        type={CALLBACK_BUTTON}
        label="With confirm"
        confirm={true}
        callback={() => true}
      />
      <Button
        className="btn btn-primary"
        type={CALLBACK_BUTTON}
        label="With confirm (custom message)"
        confirm={{
          title: 'Lorem ipsum dolor sit amet',
          message: 'Lorem ipsum dolor sit amet'
        }}
        callback={() => true}
      />
    </div>
    <div>
      <Button
        type={MENU_BUTTON}
        icon="fa fa-fw fa-ellipsis-v"
        label={trans('show-more-actions', {}, 'actions')}
        menu={{
          items: [
            {
              name: 'primary',
              type: CALLBACK_BUTTON,
              label: 'Primary action',
              callback: () => true,
              primary: true
            }, {
              name: 'other-1',
              type: CALLBACK_BUTTON,
              label: 'Other action 1',
              callback: () => true,
              group: 'Group 1'
            }, {
              name: 'other-2',
              type: CALLBACK_BUTTON,
              label: 'Other action 2',
              callback: () => true,
              group: 'Group 1'
            }, {
              name: 'other-3',
              type: CALLBACK_BUTTON,
              label: 'Other action 3',
              callback: () => true,
              group: 'Group 2'
            }, {
              name: 'disabled',
              type: CALLBACK_BUTTON,
              label: 'Disabled action',
              callback: () => true,
              disabled: true,
              group: 'Group 2'
            }, {
              name: 'dangerous',
              type: CALLBACK_BUTTON,
              label: 'Dangerous action',
              callback: () => true,
              dangerous: true
            }
          ]
        }}
      />
    </div>
  </Fragment>

export {
  ExampleButtons
}
