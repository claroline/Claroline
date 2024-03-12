import React, {Fragment} from 'react'
import classes from 'classnames'

import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MENU_BUTTON, POPOVER_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'
import {Toolbar} from '#/main/app/action'
import {ContentTitle} from '#/main/app/content/components/title'

const ExampleButtons = () =>
  <Fragment>
    <ContentTitle level={2} title="Button variants" />
    <ContentTitle level={3} title="btn" />

    <div className="btn-toolbar gap-1 mb-3">
      {['primary', 'secondary', 'success', 'danger', 'warning', 'info'].map(type =>
        <Button
          key={type}
          className={classes('btn btn-'+type)}
          type={CALLBACK_BUTTON}
          label={type.charAt(0).toUpperCase() + type.slice(1)}
          callback={() => true}
        />
      )}
    </div>

    <div className="btn-toolbar gap-1 mb-3">
      {['primary', 'secondary', 'success', 'danger', 'warning', 'info'].map(type =>
        <Button
          key={type}
          className={classes('btn btn-wave btn-'+type)}
          type={CALLBACK_BUTTON}
          label={type.charAt(0).toUpperCase() + type.slice(1)}
          callback={() => true}
        />
      )}
    </div>

    <ContentTitle level={3} title="btn-outline" />
    <div className="btn-toolbar gap-1 mb-3">
      {['primary', 'secondary', 'success', 'danger', 'warning', 'info'].map(type =>
        <Button
          key={type}
          className={classes('btn btn-outline-'+type)}
          type={CALLBACK_BUTTON}
          label={type.charAt(0).toUpperCase() + type.slice(1)}
          callback={() => true}
        />
      )}
    </div>

    <ContentTitle level={3} title="btn-text" />
    <div className="btn-toolbar gap-1 mb-3">
      {['primary', 'secondary', 'success', 'danger', 'warning', 'info'].map(type =>
        <Button
          key={type}
          className={classes('btn btn-text-'+type)}
          type={CALLBACK_BUTTON}
          label={type.charAt(0).toUpperCase() + type.slice(1)}
          callback={() => true}
        />
      )}
    </div>

    <ContentTitle level={2} title="Special buttons" />
    <div className="btn-toolbar gap-1 mb-3">
      {['body', 'subtitles'].map(type =>
        <Button
          key={type}
          className={classes('btn btn-text-'+type)}
          type={CALLBACK_BUTTON}
          label={type.charAt(0).toUpperCase() + type.slice(1)}
          callback={() => true}
        />
      )}

      <Button
        className="btn btn-link"
        type={CALLBACK_BUTTON}
        label="Link"
        callback={() => true}
      />
    </div>

    <ContentTitle level={2} title="Button sizes" />

    <div className="mb-3">
      {['sm', undefined, 'lg'].map(size =>
        <div className="mb-1" key={size || 'md'}>
          <Button
            variant="btn"
            primary={true}
            type={CALLBACK_BUTTON}
            label={(size || 'md').charAt(0).toUpperCase() + (size || 'md').slice(1) + ' button'}
            callback={() => true}
            size={size}
          />
        </div>
      )}
    </div>

    <div className="mb-3">
      <Button
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-bomb"
        label="Tooltip"
        variant="btn"
        primary={true}
        tooltip="bottom"
        callback={() => true}
      />
    </div>

    <div className="btn-toolbar gap-1 mb-3">
      <Button
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-warning"
        variant="btn"
        primary={true}
        label="With confirm"
        confirm={true}
        callback={() => true}
      />
      <Button
        type={CALLBACK_BUTTON}
        label="With confirm (custom message)"
        variant="btn"
        confirm={{
          title: 'Lorem ipsum dolor sit amet',
          message: 'Lorem ipsum dolor sit amet'
        }}
        callback={() => true}
      />
    </div>

    <div className="btn-toolbar gap-1 mb-3">
      <Button
        type={MENU_BUTTON}
        icon="fa fa-fw fa-ellipsis-v"
        label={trans('show-more-actions', {}, 'actions')}
        variant="btn"
        menu={{
          items: [
            {
              name: 'primary',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-bomb',
              label: 'Primary action',
              callback: () => true,
              primary: true
            }, {
              name: 'other-1',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-bomb',
              label: 'Other action 1',
              callback: () => true,
              group: 'Group 1'
            }, {
              name: 'other-2',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-bomb',
              label: 'Other action 2',
              callback: () => true,
              group: 'Group 1'
            }, {
              name: 'other-3',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-bomb',
              label: 'Other action 3',
              callback: () => true,
              group: 'Group 2'
            }, {
              name: 'disabled',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-bomb',
              label: 'Disabled action',
              callback: () => true,
              disabled: true,
              group: 'Group 2'
            }, {
              name: 'dangerous',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-bomb',
              label: 'Dangerous action',
              callback: () => true,
              dangerous: true,
              confirm: true
            }
          ]
        }}
      />
    </div>

    <div className="btn-toolbar gap-1 mb-3">
      {['top', 'bottom', 'left', 'right'].map((position) =>
        <Button
          key={position}
          type={POPOVER_BUTTON}
          label={`Popover ${position}`}
          variant="btn"
          popover={{
            label: 'My popover title',
            position: position,
            content: 'The content of the popover. With more text to show how it breaks with long strings.'
          }}
        />
      )}
    </div>

    <Toolbar
      variant="btn"
      actions={[
        {
          name: 'more',
          type: MENU_BUTTON,
          icon: 'fa fa-fw fa-ellipsis-v',
          label: trans('show-more-actions', {}, 'actions'),
          menu: {
            items: [
              {
                name: 'primary',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-bomb',
                label: 'Primary action',
                callback: () => true,
                primary: true
              }, {
                name: 'other-1',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-bomb',
                label: 'Other action 1',
                callback: () => true,
                group: 'Group 1'
              }, {
                name: 'other-2',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-bomb',
                label: 'Other action 2',
                callback: () => true,
                group: 'Group 1'
              }, {
                name: 'other-3',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-bomb',
                label: 'Other action 3',
                callback: () => true,
                group: 'Group 2'
              }, {
                name: 'disabled',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-bomb',
                label: 'Disabled action',
                callback: () => true,
                disabled: true,
                group: 'Group 2'
              }, {
                name: 'dangerous',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-bomb',
                label: 'Dangerous action',
                callback: () => true,
                dangerous: true,
                confirm: true
              }
            ]
          }
        }
      ]}
    />

    <div>
      <Toolbar
        toolbar="more"
        variant="btn"
        actions={[
          {
            name: 'primary',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-bomb',
            label: 'Primary action',
            callback: () => true,
            primary: true
          }, {
            name: 'other-1',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-bomb',
            label: 'Other action 1',
            callback: () => true,
            group: 'Group 1'
          }, {
            name: 'other-2',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-bomb',
            label: 'Other action 2',
            callback: () => true,
            group: 'Group 1'
          }, {
            name: 'other-3',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-bomb',
            label: 'Other action 3',
            callback: () => true,
            group: 'Group 2'
          }, {
            name: 'disabled',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-bomb',
            label: 'Disabled action',
            callback: () => true,
            disabled: true,
            group: 'Group 2'
          }, {
            name: 'dangerous',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-bomb',
            label: 'Dangerous action',
            callback: () => true,
            dangerous: true,
            confirm: true
          }
        ]}
      />
    </div>
  </Fragment>

export {
  ExampleButtons
}
