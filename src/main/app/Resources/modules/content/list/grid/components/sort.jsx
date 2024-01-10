import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'

import {DataListProperty} from '#/main/app/content/list/prop-types'
import {getPropDefinition} from '#/main/app/content/list/utils'

const GridSort = props =>
  <div className="data-grid-sort">
    <span className="d-none d-sm-block">{trans('list_sort_by')}</span>

    <Button
      id="data-grid-sort-menu"
      className="btn btn-text-primary fw-bold"
      type={MENU_BUTTON}
      label={props.current.property && getPropDefinition(props.current.property, props.available) ?
        getPropDefinition(props.current.property, props.available).label :
        trans('none')
      }
      primary={true}
      menu={{
        label: trans('list_columns'),
        align: 'right',
        items: props.available.map(column => ({
          type: CALLBACK_BUTTON,
          label: column.label,
          active: props.current.property && (props.current.property === column.alias || props.current.property === column.name),
          callback: () => props.updateSort(column.alias ? column.alias : column.name, props.current.direction || 1)
        }))
      }}
    />

    <div className="vr" role="presentation" />

    <Button
      className="btn btn-text-primary"
      type={CALLBACK_BUTTON}
      icon={classes('fa fa-fw', {
        'fa-sort'     :  0 === props.current.direction || !props.current.direction,
        'fa-sort-asc' :  1 === props.current.direction,
        'fa-sort-desc': -1 === props.current.direction
      })}
      label={trans('sort', {}, 'actions')}
      disabled={!props.current.property}
      callback={() => {
        let direction = 1
        if (1 === props.current.direction) {
          direction = -1
        } else if (-1 === props.current.direction) {
          direction = 0
        }

        props.updateSort(props.current.property, direction)
      }}
      tooltip="left"
      primary={true}
    />
  </div>

GridSort.propTypes = {
  current: T.shape({
    property: T.string,
    direction: T.number
  }).isRequired,
  available: T.arrayOf(
    T.shape(DataListProperty.propTypes)
  ).isRequired,
  updateSort: T.func.isRequired
}

export {
  GridSort
}
