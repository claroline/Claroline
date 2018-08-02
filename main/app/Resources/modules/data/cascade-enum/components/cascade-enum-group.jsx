import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/core/translation'
import {FormGroup as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button'
import {TextGroup}  from '#/main/core/layout/form/components/group/text-group'

const EnumChildren = props =>
  <ul className="enum-children-list">
    {props.item.children.map((child, index) =>
      <EnumItem
        key={`item-${child.id}`}
        indexes={props.indexes.concat([index])}
        item={child}
        level={props.level}
        addChildButtonLabel={props.addChildButtonLabel}
        deleteButtonLabel={props.deleteButtonLabel}
        validating={props.validating}
        error={props.error && typeof props.error !== 'string' ? props.error : undefined}
        onChange={props.onChange}
        onDelete={props.onDelete}
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
  addChildButtonLabel: T.string.isRequired,
  deleteButtonLabel: T.string.isRequired,
  error: T.object,
  validating: T.bool,
  onChange: T.func.isRequired,
  onDelete: T.func.isRequired
}

const EnumItem = props =>
  <li className={classes('cascade-enum-item', {'item-root': props.level === 1, 'item-child': props.level > 1})}>
    <div className="item-container">
      <TextGroup
        id={`item-${props.item.id}-value`}
        className="enum-item-content"
        label={`${trans('choice')}-${props.indexes.join('-')}`}
        hideLabel={true}
        value={props.item.value}
        onChange={value => {
          const newItem = Object.assign({}, props.item, {value: value})
          props.onChange(newItem, props.indexes)
        }}
        warnOnly={!props.validating}
        error={props.error ? props.error[props.item.id] : props.error}
      />

      <div className="right-controls">
        <TooltipButton
          id={`enum-item-${props.item.id}-add`}
          className="btn-link-default"
          title={props.addChildButtonLabel}
          onClick={() => {
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
          }}
        >
          <span className="fa fa-fw fa-plus" />
        </TooltipButton>
        <TooltipButton
          id={`enum-item-${props.item.id}-delete`}
          className="btn-link-danger"
          title={props.deleteButtonLabel}
          onClick={() => props.onDelete(props.indexes)}
        >
          <span className="fa fa-fw fa-trash-o" />
        </TooltipButton>
      </div>
    </div>

    {props.item.children && props.item.children.length > 0 &&
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

EnumItem.propTypes = {
  indexes: T.array.isRequired,
  item: T.shape({
    id: T.string,
    value: T.string,
    children: T.array
  }).isRequired,
  level: T.number.isRequired,
  addChildButtonLabel: T.string.isRequired,
  deleteButtonLabel: T.string.isRequired,
  error: T.object,
  validating: T.bool,
  onChange: T.func.isRequired,
  onDelete: T.func.isRequired
}

EnumItem.defaultTypes = {
  addChildButtonLabel: trans('add_a_sub_item'),
  deleteButtonLabel: trans('delete')
}

const CascadeEnumGroup = (props) =>
  <FormGroup
    {...props}
    error={props.error && typeof props.error === 'string' ? props.error : undefined}
    className="cascade-enum-group"
  >
    {props.value.length > 0 &&
      <ul>
        {props.value.map((item, index) =>
          <EnumItem
            key={`item-${item.id}`}
            indexes={[index]}
            item={item}
            level={1}
            addChildButtonLabel={props.addChildButtonLabel}
            deleteButtonLabel={props.deleteButtonLabel}
            validating={props.validating}
            error={props.error && typeof props.error !== 'string' ? props.error : undefined}
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
      <div className="no-item-info">
        {props.placeholder}
      </div>
    }

    <button
      className="btn btn-block btn-default"
      type="button"
      onClick={() => props.onChange([].concat(props.value, [{
        id: makeId(),
        value: '',
        children: []
      }]))}
    >
      <span className="fa fa-fw fa-plus icon-with-text-right" />
      {props.addButtonLabel}
    </button>
  </FormGroup>

implementPropTypes(CascadeEnumGroup, FormGroupWithFieldTypes, {
  value: T.arrayOf(T.shape({
    id: T.string,
    value: T.string
  })),
  error: T.oneOfType([T.string, T.object]),
  addButtonLabel: T.string.isRequired,
  addChildButtonLabel: T.string.isRequired,
  deleteButtonLabel: T.string.isRequired
}, {
  value: [],
  placeholder: trans('no_item'),
  addButtonLabel: trans('add_an_item'),
  addChildButtonLabel: trans('add_a_sub_item'),
  deleteButtonLabel: trans('delete')
})

export {
  CascadeEnumGroup
}
