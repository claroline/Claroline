import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {GroupCard} from '#/main/community/group/components/card'
import {route} from '#/main/community/group/routing'

import {isFull} from '#/plugin/cursus/utils'
import {Session as SessionTypes} from '#/plugin/cursus/prop-types'

const RegistrationGroups = (props) =>
  <Fragment>
    {isFull(props.session) && hasPermission('register', props.session) &&
      <AlertBlock type="warning" title={trans('session_full', {}, 'cursus')}>
        {trans('session_full_group_help', {}, 'cursus')}
      </AlertBlock>
    }

    <ListData
      className="component-container"
      name={props.name}
      fetch={{
        url: props.url,
        autoload: true
      }}
      delete={{
        url: props.unregisterUrl,
        label: trans('unregister', {}, 'actions'),
        displayed: () => hasPermission('register', props.session)
      }}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        label: trans('show_profile', {}, 'actions'),
        target: route(row.group)
      })}
      definition={[
        {
          name: 'group',
          type: 'group',
          label: trans('group'),
          displayed: true
        }, {
          name: 'date',
          type: 'date',
          label: trans('registration_date', {}, 'cursus'),
          options: {time: true},
          displayed: true
        }
      ].concat(props.customDefinition || [])}
      actions={props.actions}
      card={(cardProps) => <GroupCard {...cardProps} data={cardProps.data.group} />}
    />

    {props.add &&
      <Button
        {...props.add}
        className="btn btn-block btn-emphasis component-container"
        primary={true}
        disabled={isFull(props.session)}
      />
    }
  </Fragment>

RegistrationGroups.propTypes = {
  session: T.shape(
    SessionTypes.propTypes
  ).isRequired,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  unregisterUrl: T.oneOfType([T.string, T.array]).isRequired,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  })),
  actions: T.func,
  add: T.shape({
    // action types
  })
}

export {
  RegistrationGroups
}
