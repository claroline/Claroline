import React, {cloneElement, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl'
import {constants} from '#/main/app/constants'
import {Button} from '#/main/app/action'
import {Thumbnail} from '#/main/app/components/thumbnail'
import {SearchMinimal} from '#/main/app/content/search/components/minimal'

const ContentMenuSkeleton = ({
  className
}) => {
  return (
    <div className={className} role="presentation">
    </div>
  )
}

ContentMenuSkeleton.propTypes = {
  className: T.string,
  search: T.bool
}

const ContentMenuItem = (props) =>
  <div className={classes('list-group', props.className)} role="presentation">
    <Button
      {...props.action}
      id={props.id}
      className="list-group-item list-group-item-action d-flex gap-3 align-items-center focus-ring"
      autoFocus={props.autoFocus}
      icon={props.icon &&
        <Thumbnail square={true} size="sm" color={props.color}>
          {typeof props.icon === 'string' ?
            <span className={`fa fa-${props.icon}`} /> :
            cloneElement(props.icon, {size: 'sm'})
          }
        </Thumbnail>
      }
      label={
        <>
          <div className="flex-fill" role="presentation">
            <b>
              {props.label}
              {props.advanced &&
                <span className="badge bg-primary-subtle text-primary-emphasis ms-2">{trans('advanced')}</span>
              }
            </b>
            {props.description &&
              <p className="mb-0 text-body-secondary fs-sm" dangerouslySetInnerHTML={{ __html: props.description }} />
            }
          </div>

          <span className="fa fa-chevron-right text-body-tertiary" aria-hidden={true} role="presentation" />
        </>
      }
    />
  </div>

ContentMenuItem.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  icon: T.oneOfType([T.string, T.node]),
  color: T.string,
  label: T.string.isRequired,
  description: T.string,
  advanced: T.bool,
  autoFocus: T.bool,
  action: T.shape({
    type: T.string.isRequired
  })
}

const ContentMenu = ({
  className,
  items,
  searchPlaceholder,
  search = false,
  color = true,
  autoFocus = true
}) => {
  const [searchStr, setSearch] = useState('')

  const displayedTypes = items
    .filter(action => undefined === action.displayed || action.displayed)
    .filter(action => 3 > searchStr.length
      || action.label.toLowerCase().includes(searchStr.toLowerCase())
      || (action.description && action.description.toLowerCase().includes(searchStr.toLowerCase()))
    )

  const unclassifiedTypes = displayedTypes
    .filter(action => !action.group)

  // generate actions groups
  const groupedTypes = {}
  for (let i=0; i < displayedTypes.length; i++) {
    const action = displayedTypes[i]
    if (action.group) {
      if (!groupedTypes[action.group]) {
        groupedTypes[action.group] = []
      }

      groupedTypes[action.group].push(action)
    }
  }

  return (
    <div className={className} role="presentation">
      {search &&
        <SearchMinimal
          className="mb-4"
          search={searchStr}
          onSearch={setSearch}
          autoFocus={autoFocus}
          placeholder={searchPlaceholder}
        />
      }

      {unclassifiedTypes.map((creationType, index) =>
        <ContentMenuItem
          key={creationType.id}
          className={0 !== index ? 'mt-2' : undefined}
          autoFocus={!search && autoFocus && 0 === index}
          color={color ? constants.COLORS[index % constants.COLORS.length] : undefined}
          {...creationType}
        />
      )}

      {Object.keys(groupedTypes).map((group) => [
        <div key={group} className="fs-sm text-body-secondary text-uppercase fw-semibold mt-5 mb-3">{group}</div>,
        ...groupedTypes[group].map((creationType, index) =>
          <ContentMenuItem
            key={creationType.id}
            className={0 !== index ? 'mt-2' : undefined}
            color={color ? constants.COLORS[unclassifiedTypes.length + index] : undefined}
            {...creationType}
          />
        )
      ])}
    </div>
  )
}

ContentMenu.propTypes = {
  autoFocus: T.bool,
  className: T.string,
  search: T.bool,
  searchPlaceholder: T.string,
  items: T.arrayOf(T.shape({
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

export {
  ContentMenu
}
