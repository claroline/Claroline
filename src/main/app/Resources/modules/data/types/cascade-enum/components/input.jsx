import React, {useState} from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/app/intl/translation'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {Button, Toolbar} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {DataInput} from '#/main/app/data/components/input'

const EnumChildren = props =>
  <ul className="enum-children-list mt-2">
    {props.item.children.map((child, index) =>
      <EnumItem
        key={`item-${child.id}`}
        indexes={props.indexes.concat([index])}
        item={child}
        level={props.level}
        validating={props.validating}
        error={props.error && typeof props.error !== 'string' ? props.error : undefined}
        onChange={props.onChange}
        onDelete={props.onDelete}
        addChildButtonLabel={props.addChildButtonLabel}
        deleteButtonLabel={props.deleteButtonLabel}
      />
    )}
  </ul>

EnumChildren.propTypes = {
  indexes: T.array.isRequired,
  item: T.shape({
    id: T.string,
    value: T.string,
    children: T.array.isRequired
  }).isRequired,
  level: T.number.isRequired,
  error: T.object,
  validating: T.bool,
  onChange: T.func.isRequired,
  onDelete: T.func.isRequired,
  addChildButtonLabel: T.string,
  deleteButtonLabel: T.string
}

const EnumItem = (props) => {
  const [collapsed, setCollapsed] = useState(false)

  return (
    <li className={classes('cascade-enum-item mb-2', {
      'item-root': props.level === 1,
      'item-child': props.level > 1
    })}>
      <div className="item-container">
        <DataInput
          id={`item-${props.item.id}-value`}
          className="enum-item-content"
          type="string"
          label={`${trans('choice')}-${props.indexes.join('-')}`}
          hideLabel={true}
          value={props.item.value}
          onChange={value => {
            const newItem = Object.assign({}, props.item, {value: value})
            props.onChange(newItem, props.indexes)
          }}
          validating={!props.validating}
          error={props.error ? props.error[props.item.id] : props.error}
        />

        <Toolbar
          id={`${props.item.id}-actions`}
          className="right-controls"
          buttonName="btn"
          tooltip="top"
          actions={[
            {
              name: 'toggle',
              type: CALLBACK_BUTTON,
              className: 'btn-text-body',
              icon: classes('fa fa-fw', {
                'fa-caret-right': collapsed,
                'fa-caret-down': !collapsed
              }),
              label: trans(collapsed ? 'expand':'collapse', {}, 'actions'),
              callback: () => setCollapsed(!collapsed),
              displayed: props.item.children && props.item.children.length > 0
            }, {
              name: 'add',
              type: CALLBACK_BUTTON,
              className: 'btn-text-body',
              icon: 'fa fa-fw fa-plus',
              label: props.addChildButtonLabel,
              callback: () => {
                const newItem = Object.assign({}, props.item)
                const newChild = {
                  id: makeId(),
                  value: '',
                  children: []
                }

                if (!newItem.children) {
                  newItem.children = []
                }
                newItem.children.push(newChild)
                props.onChange(newItem, props.indexes)
              }
            }, {
              name: 'delete',
              type: CALLBACK_BUTTON,
              className: 'btn-text-danger',
              icon: 'fa fa-fw fa-trash',
              label: props.deleteButtonLabel,
              callback: () => props.onDelete(props.indexes),
              dangerous: true
            }
          ]}
        />
      </div>

      {!collapsed && props.item.children && props.item.children.length > 0 &&
        <EnumChildren
          {...props}
          indexes={props.indexes}
          item={props.item}
          level={props.level + 1}
          validating={props.validating}
          error={props.error}
        />
      }
    </li>
  )
}

EnumItem.propTypes = {
  indexes: T.array.isRequired,
  item: T.shape({
    id: T.string,
    value: T.string,
    children: T.array
  }).isRequired,
  level: T.number.isRequired,
  error: T.object,
  validating: T.bool,
  onChange: T.func.isRequired,
  onDelete: T.func.isRequired,
  addChildButtonLabel: T.string,
  deleteButtonLabel: T.string
}

const CascadeEnumInput = (props) =>
  <div className="cascade-enum-group">
    {props.value.length > 0 &&
      <ul>
        {props.value.map((item, index) =>
          <EnumItem
            key={`item-${item.id}`}
            indexes={[index]}
            item={item}
            level={1}
            validating={props.validating}
            error={props.error && typeof props.error !== 'string' ? props.error : undefined}
            addChildButtonLabel={props.addChildButtonLabel}
            deleteButtonLabel={props.deleteButtonLabel}
            onChange={(newItem, indexes) => {
              const newValue = props.value.slice()

              if (indexes.length === 1) {
                newValue[indexes[0]] = newItem
              } else if (indexes.length > 1) {
                let current = newValue[indexes[0]]

                for (let i = 1; i < indexes.length - 1; ++i) {
                  current = current.children[indexes[i]]
                }
                current.children[indexes[indexes.length - 1]] = newItem
              }
              props.onChange(newValue)
            }}
            onDelete={(indexes) => {
              const newValue = props.value.slice()

              if (indexes.length === 1) {
                newValue.splice(indexes[0], 1)
                // newValue[indexes[0]] = newItem
              } else if (indexes.length > 1) {
                let current = newValue[indexes[0]]

                for (let i = 1; i < indexes.length - 1; ++i) {
                  current = current.children[indexes[i]]
                }
                // current.children[indexes[indexes.length - 1]] = newItem
                current.children.splice(indexes[indexes.length - 1], 1)
              }
              props.onChange(newValue)
            }}
          />
        )}
      </ul>
    }

    {props.value.length === 0 &&
      <ContentPlaceholder title={props.placeholder} size={props.size} />
    }

    <Button
      className="btn btn-outline-primary w-100 mt-3"
      type={CALLBACK_BUTTON}
      icon="fa fa-fw fa-plus"
      label={props.addButtonLabel}
      callback={() => props.onChange([].concat(props.value, [{
        id: makeId(),
        value: '',
        children: []
      }]))}
    />
  </div>

implementPropTypes(CascadeEnumInput, DataInputTypes, {
  value: T.arrayOf(T.shape({
    id: T.string,
    value: T.string
  })),
  addButtonLabel: T.string,
  addChildButtonLabel: T.string,
  deleteButtonLabel: T.string
}, {
  value: [],
  placeholder: trans('no_item'),
  addButtonLabel: trans('add_an_item'),
  addChildButtonLabel: trans('add_a_sub_item'),
  deleteButtonLabel: trans('delete', {}, 'actions')
})

export {
  CascadeEnumInput
}
