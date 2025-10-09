import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {Button, Toolbar} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

const TreeItem = ({
  item,
  size,
  onClick
}) => {
  const [expanded, setExpanded] = useState(true)

  return (
    <li>
      <div className="d-flex flex-row flex-nowrap align-items-center gap-1" role="presentation">
        {!isEmpty(item.children) ?
          <Button
            className="btn btn-text-body flex-shrink-0 px-1 py-1 focus-ring border-0"
            type={CALLBACK_BUTTON}
            icon={classes({
              'fa fa-fw fa-chevron-right': !expanded,
              'fa fa-fw fa-chevron-down': expanded
            })}
            tooltip="bottom"
            label={trans(expanded ? 'collapse':'expand', {}, 'actions')}
            callback={() => setExpanded(!expanded)}
            size="sm"
          /> :
          <span className="fs-sm px-1 py-1" aria-hidden={true}>
            <span className="fa fa-fw" />
          </span>
        }

        <Button
          className={classes('btn btn-text-body text-truncate px-1 focus-ring flex-fill text-start', {
            'py-1': 'sm' === size,
            'py-2': 'sm' !== size
          })}
          {...omit(item, 'className', 'children', 'actions')}
          size={size}
          onClick={onClick}
        />

        {!isEmpty(item.actions) &&
          <Toolbar
            className="ms-auto flex-shrink-0"
            buttonName={classes('btn btn-text-body px-1 py-2 focus-ring', {
              'py-1': 'sm' === size,
              'py-2': 'sm' !== size
            })}
            toolbar="more"
            tooltip="bottom"
            actions={item.actions}
            size="sm"
          />
        }
      </div>

      {expanded && !isEmpty(item.children) &&
        <ul className="list-unstyled ps-4">
          {item.children
            .filter(child => undefined === child.displayed || child.displayed)
            .map(child =>
              <TreeItem
                key={child.id}
                item={child}
                size={size}
                onClick={onClick}
              />
            )
          }
        </ul>
      }
    </li>
  )
}

const Tree = ({
  size,
  onClick,
  items = [],
  className = null
}) => {
  if (isEmpty(items)) {
    return null
  }

  return (
    <ul className={classes('list-unstyled', className)}>
      {items
        .filter(item => undefined === item.displayed || item.displayed)
        .map(item =>
          <TreeItem
            key={item.id}
            item={item}
            size={size}
            onClick={onClick}
          />
        )
      }
    </ul>
  )
}

Tree.propTypes = {
  className: T.string,
  size: T.oneOf(['sm']),
  items: T.arrayOf(T.shape({
    id: T.string.isRequired,
    label: T.oneOfType([T.string, T.node]).isRequired,
    children: T.array,
    displayed: T.bool
  })),
  onClick: T.func
}

export {
  Tree
}
