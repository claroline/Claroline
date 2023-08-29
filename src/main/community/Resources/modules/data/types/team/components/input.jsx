import React, {Fragment} from 'react'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {TeamCard} from '#/main/community/team/components/card'
import {Team as TeamType} from '#/main/community/prop-types'
import {MODAL_TEAMS} from '#/main/community/modals/teams'

const TeamButton = props =>
  <Button
    className="btn btn-outline-primary w-100 mt-2"
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-plus"
    label={trans('add_team')}
    disabled={props.disabled}
    modal={[MODAL_TEAMS, {
      url: props.url,
      title: props.title,
      filters: props.filters,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected)
      })
    }]}
    size={props.size}
  />

TeamButton.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  filters: T.arrayOf(T.shape({
    // TODO : list filter types
  })),
  disabled: T.bool,
  onChange: T.func.isRequired,
  size: T.string
}

const TeamInput = props => {
  if (!isEmpty(props.value)) {
    return(
      <Fragment>
        {props.value.map(team =>
          <TeamCard
            key={`team-card-${team.id}`}
            data={team}
            size="xs"
            actions={[
              {
                name: 'delete',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-trash',
                label: trans('delete', {}, 'actions'),
                dangerous: true,
                disabled: props.disabled,
                callback: () => {
                  const newValue = props.value
                  const index = newValue.findIndex(t => t.id === team.id)

                  if (-1 < index) {
                    newValue.splice(index, 1)
                    props.onChange(newValue)
                  }
                }
              }
            ]}
          />
        )}

        <TeamButton
          {...props.picker}
          disabled={props.disabled}
          size={props.size}
          onChange={(selected) => {
            const newValue = props.value
            selected.forEach(team => {
              const index = newValue.findIndex(t => t.id === team.id)

              if (-1 === index) {
                newValue.push(team)
              }
            })
            props.onChange(newValue)
          }}
        />
      </Fragment>
    )
  }

  return (
    <ContentPlaceholder
      icon="fa fa-users"
      title={trans('no_group')}
      size={props.size}
    >
      <TeamButton
        {...props.picker}
        size={props.size}
        disabled={props.disabled}
        onChange={props.onChange}
      />
    </ContentPlaceholder>
  )
}

implementPropTypes(TeamInput, DataInputTypes, {
  value: T.arrayOf(T.shape(
    TeamType.propTypes
  )),
  picker: T.shape({
    url: T.oneOfType([T.string, T.array]),
    title: T.string,
    filters: T.arrayOf(T.shape({
      // TODO : list filter types
    }))
  })
}, {
  value: null,
  picker: {}
})

export {
  TeamInput
}
