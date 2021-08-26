import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {Button} from '#/main/app/action/components/button'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'
import {GroupCard} from '#/main/core/user/data/components/group-card'

import {isFull} from '#/plugin/cursus/utils'
import {Session as SessionTypes} from '#/plugin/cursus/prop-types'

const SessionGroups = (props) =>
  <Fragment>
    {isFull(props.session) && hasPermission('register', props.session) &&
      <AlertBlock type="warning" title={trans('La session est complète.', {}, 'cursus')}>
        {trans('Il n\'est plus possible d\'inscrire de nouveaux groupes.', {}, 'cursus')}
      </AlertBlock>
    }

    <ListData
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
      ]}
      actions={props.actions}
      card={(cardProps) => <GroupCard {...cardProps} data={cardProps.data.group} />}
      display={{
        current: listConst.DISPLAY_TILES_SM
      }}
    />

    {props.add && hasPermission('register', props.session) &&
      <Button
        className="btn btn-block btn-emphasis component-container"
        primary={true}
        {...props.add}
      />
    }
  </Fragment>

SessionGroups.propTypes = {
  session: T.shape(
    SessionTypes.propTypes
  ).isRequired,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  unregisterUrl: T.oneOfType([T.string, T.array]).isRequired,
  actions: T.func,
  add: T.shape({
    // action types
  })
}

export {
  SessionGroups
}
