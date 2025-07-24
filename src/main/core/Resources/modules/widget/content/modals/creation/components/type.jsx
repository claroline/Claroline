import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ContentMenu} from '#/main/app/content/components/menu'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {WidgetContentIcon, WidgetSourceIcon} from '#/main/core/widget/content/components/icon'
import {Widget as WidgetTypes} from '#/main/core/widget/prop-types'

const ContentType = props => {
  return (
    <div className="modal-body" role="presentation">
      <ContentMenu
        className="mb-3"
        color={false}
        search={true}
        items={[].concat(props.types.map(type => ({
          id: type.name,
          icon: React.createElement(WidgetContentIcon, {
            type: type.name
          }),
          displayed: isEmpty(type.sources),
          label: trans(type.name, {}, 'widget'),
          description: trans(`${type.name}_desc`, {}, 'widget'),
          action: {
            type: CALLBACK_BUTTON,
            callback: () => props.select(type.name)
          }
        })), props.sources.map(source => {
          const widget = props.types.find(widget => widget.sources && widget.sources.includes(source.type))

          return {
            id: source.name,
            icon: React.createElement(WidgetSourceIcon, {
              type: source.name
            }),
            label: trans(source.name, {}, 'data_sources'),
            description: trans(`${source.name}_desc`, {}, 'data_sources'),
            action: {
              type: CALLBACK_BUTTON,
              callback: () => props.select(widget.name, source.name)
            },
            group: trans(widget.name, {}, 'widget'),
          }
        }))}
      />
    </div>
  )
}

ContentType.propTypes = {
  types: T.arrayOf(T.shape(
    WidgetTypes.propTypes
  )).isRequired,
  sources: T.arrayOf(T.shape({
    name: T.string.isRequired,
    type: T.string.isRequired
  })),
  select: T.func.isRequired
}

export {
  ContentType
}
