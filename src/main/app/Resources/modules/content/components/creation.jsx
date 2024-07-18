import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {Thumbnail} from '#/main/app/components/thumbnail'

const COLORS = [
  'var(--bs-pink)',
  'var(--bs-cyan)',
  'var(--bs-purple)',
  'var(--bs-teal)',
  'var(--bs-orange)',
]

const ContentCreationType = (props) =>
  <div className={classes('list-group', props.className)} role="presentation">
    <Button
      {...props.action}
      id={props.id}
      className="list-group-item list-group-item-action d-flex gap-3 align-items-center"
      autoFocus={props.autoFocus}
      icon={props.icon &&
        <Thumbnail square={true} size="sm" color={props.color}>
          {typeof props.icon === 'string' ?
            <span className={`fa fa-${props.icon}`} /> :
            props.icon
          }
        </Thumbnail>
      }
      label={
        <>
          <div className="flex-fill" role="presentation">
            <b className="mb-2">
              {props.label}
              {props.advanced &&
                <span className="badge bg-primary-subtle text-primary-emphasis ms-2">{trans('advanced')}</span>
              }
            </b>
            <p className="mb-0 text-body-secondary fs-sm" dangerouslySetInnerHTML={{ __html: props.description }} />
          </div>

          <span className="fa fa-chevron-right text-body-tertiary" aria-hidden={true} role="presentation" />
        </>
      }
    />
  </div>

ContentCreationType.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  icon: T.oneOfType([T.string, T.node]),
  color: T.string,
  label: T.string.isRequired,
  description: T.string.isRequired,
  advanced: T.bool,
  autoFocus: T.bool,
  action: T.shape({
    type: T.string.isRequired
  })
}

const ContentCreation = (props) => {
  const displayedTypes = props.types.filter(
    action => undefined === action.displayed || action.displayed
  )

  const unclassifiedTypes = displayedTypes
    .filter(action => !action.group)

  // generate actions groups
  const groupedTypes = {}
  for (let i=0; i < displayedTypes.length; i++) {
    const action = displayedTypes[i]
    if (!!action.group) {
      if (!groupedTypes[action.group]) {
        groupedTypes[action.group] = []
      }

      groupedTypes[action.group].push(action)
    }
  }

  return (
    <div className={props.className} role="presentation">
      {unclassifiedTypes.map((creationType, index) =>
        <ContentCreationType
          key={creationType.id}
          className={0 !== index ? 'mt-2' : undefined}
          autoFocus={0 === index}
          color={props.color ? COLORS[index] : undefined}
          {...creationType}
        />
      )}

      {Object.keys(groupedTypes).map((group) => [
        <div key={group} className="fs-sm text-body-secondary text-uppercase fw-semibold mt-5 mb-1">{group}</div>,
        ...groupedTypes[group].map((creationType, index) =>
          <ContentCreationType
            key={creationType.id}
            className={0 !== index ? 'mt-2' : undefined}
            color={props.color ? COLORS[unclassifiedTypes.length + index] : undefined}
            {...creationType}
          />
        )
      ])}
    </div>
  )
}

ContentCreation.propTypes = {
  className: T.string,
  types: T.arrayOf(T.shape({
    id: T.string.isRequired,
    icon: T.oneOfType([T.string, T.node]),
    label: T.string.isRequired,
    description: T.string,
    advanced: T.bool,
    displayed: T.bool,
    action: T.shape({
      // Action types
    }),
    group: T.string
  })),
  color: T.bool
}

ContentCreation.defaultProps = {
  color: true
}

export {
  ContentCreation
}
