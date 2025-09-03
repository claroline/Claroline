import React, {useCallback, useMemo} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import omit from 'lodash/omit'
import isEmpty from 'lodash/isEmpty'

import {makeReducer, useReducer} from '#/main/app/store/reducer'
import {trans, transChoice} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {makeListReducer, actions as listActions, selectors as listSelectors} from '#/main/app/content/list/store'
import {ListData} from '#/main/app/content/list/containers/data'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {LIST_TOGGLE_SELECT, LIST_TOGGLE_SELECT_ALL} from '#/main/app/content/list/store/actions'
import {makeInstanceAction} from '#/main/app/store/actions'
import {constants as listConst} from '#/main/app/content/list'

const PickerModal = (props) => {
  // append list reducer to the store if not already mounted
  const reducer = useMemo(() => makeListReducer(props.name, {
    sortBy: props.sortBy || null,
    filters: {filters: props.filters || []}
  }, {
    selected: makeReducer([], {
      [makeInstanceAction(LIST_TOGGLE_SELECT, props.name)]: (state, action) => {
        if (!props.multiple) {
          return [action.row.id]
        }

        return state
      },
      [makeInstanceAction(LIST_TOGGLE_SELECT_ALL, props.name)]: (state, action) => {
        if (!props.multiple) {
          if (!isEmpty(action.rows)) {
            return [action.rows[0].id]
          }
        }

        return state
      }
    })
  }), [props.name, props.multiple])
  useReducer(props.name, reducer)

  const dispatch = useDispatch()
  const reset = useCallback(() => {
    dispatch(listActions.resetSelect(props.name))
    dispatch(listActions.resetFilters(props.name, props.filters || []))
    dispatch(listActions.invalidateData(props.name))
  }, [props.name])
  const select = useCallback((row) => {
    dispatch(listActions.toggleSelect(props.name, row))
  }, [props.name])

  const selected = useSelector((state) => listSelectors.selectedFull(listSelectors.list(state, props.name)))
  let selectAction
  if (props.selectAction) {
    selectAction = props.selectAction(selected)
  }

  return (
    <Modal
      size="xl"
      {...omit(props, 'name', 'definition', 'card', 'url', 'selectAction', 'displayMode')}
      className="data-picker-modal"
      onExited={reset}
      centered={true}
      scrollable={true}
      enforceFocus={true}
    >
      <div className="modal-body p-0 d-flex">
        {props.definition ?
          <ListData
            fetch={{
              url: props.url,
              autoload: true
            }}
            className="border-top"
            autoFocus={true}
            name={props.name}
            flush={true}
            definition={props.definition}
            card={props.card}
            primaryAction={(row) => ({
              type: CALLBACK_BUTTON,
              label: trans('select', {}, 'actions'),
              callback: () => select(row)
            })}
            selectable={true}
            display={{current: props.displayMode}}
          /> :
          props.children
        }
      </div>

      {props.children}

      <div className="modal-footer">
        {selected && 0 !== selected.length &&
          <span role="presentation">
            {transChoice('list_selected_count', selected.length, {count: selected.length}, 'platform')}
          </span>
        }

        {selectAction &&
          <Button
            label={trans('select', {}, 'actions')}
            {...selectAction}
            className="btn btn-primary"
            disabled={0 === selected.length}
            onClick={props.fadeModal}
          />
        }
      </div>
    </Modal>
  )
}

PickerModal.propTypes = {
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]),
  multiple: T.bool,
  filters: T.array,
  sortBy: T.shape({
    property: T.string,
    direction: T.number
  }),
  definition: T.arrayOf(T.object),
  card: T.func,
  selectAction: T.func,
  displayMode: T.string,
  children: T.any,
  fadeModal: T.func.isRequired
}

PickerModal.defaultProps = {
  multiple: true,
  displayMode: listConst.DISPLAY_LIST,
  filters: []
}

export {
  PickerModal
}
