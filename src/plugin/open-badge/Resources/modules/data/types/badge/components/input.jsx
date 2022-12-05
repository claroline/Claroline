import React from 'react'

import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {Badge as BadgeType} from '#/plugin/open-badge/prop-types'
import {BadgeCard} from '#/plugin/open-badge/tools/badges/badge/components/card'

//todo: implement badge picker
const BadgeButton = () =>
  <Button
    className="btn"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-trophy"
    label={trans('select_a_badge', {}, 'badge')}
    primary={true}
  />

BadgeButton.propTypes = {
  title: T.string,
  onChange: T.func.isRequired
}

const BadgeInput = props => {
  const actions = props.disabled ? []: [
    {
      name: 'delete',
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-trash',
      label: trans('delete', {}, 'actions'),
      dangerous: true,
      callback: () => props.onChange(null)
    }
  ]

  if (props.value) {
    return(
      <div>
        <BadgeCard
          data={props.value}
          size="sm"
          orientation="col"
          actions={actions}
        />

        {!props.disabled &&
          <BadgeButton
            {...props.picker}
            onChange={props.onChange}
          />
        }
      </div>
    )
  } else {
    return (
      <ContentPlaceholder
        size="lg"
        icon="fa fa-book"
        title={trans('no_badge', {}, 'badge')}
      >
        {!props.disabled &&
          <BadgeButton
            {...props.picker}
            onChange={props.onChange}
          />
        }
      </ContentPlaceholder>
    )
  }
}

implementPropTypes(BadgeInput, DataInputTypes, {
  value: T.shape(BadgeType.propTypes),
  picker: T.shape({
    title: T.string
  })
}, {
  value: null,
  picker: {
    title: trans('badge_selector', {}, 'badge')
  }
})

export {
  BadgeInput
}
